package pi2425.swappy_javafx.controllers;
import javafx.beans.property.SimpleStringProperty;
import javafx.collections.FXCollections;
import javafx.collections.ObservableList;
import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.scene.Node;
import javafx.scene.Parent;
import javafx.scene.Scene;
import javafx.scene.control.*;
import javafx.scene.image.ImageView;
import javafx.scene.layout.HBox;
import javafx.stage.Stage;
import pi2425.swappy_javafx.entities.Event;
import pi2425.swappy_javafx.entities.UserSession;
import pi2425.swappy_javafx.services.EventService;
import pi2425.swappy_javafx.services.SessionService;
import pi2425.swappy_javafx.utils.NavigationUtils;
import java.io.IOException;
import java.sql.SQLException;
import java.util.Date;
import java.util.List;
import java.util.Optional;
import java.util.stream.Collectors;

import javafx.scene.control.cell.PropertyValueFactory;

import static pi2425.swappy_javafx.utils.NavigationUtils.showAlert;

public class EventController {

    @FXML private ImageView eventImageView;
    @FXML private TableView<Event> eventTableView;
    @FXML private TableColumn<Event, String> titleColumn;
    @FXML private TableColumn<Event, String> descColumn;
    @FXML private TableColumn<Event, String> dateDebutColumn;
    @FXML private TableColumn<Event, String> dateFinColumn;
    @FXML private TableColumn<Event, Integer> maxParticipantsColumn;
    @FXML private TableColumn<Event, String> statusColumn;
    @FXML private TableColumn<Event, Integer> nbSessionsColumn;
    @FXML private TableColumn<Event, Void> actionColumn;
    @FXML private TableView<Event> participationTableView;
    @FXML private TableColumn<Event, String> pTitleColumn;
    @FXML private TableColumn<Event, String> pDescColumn;
    @FXML private TableColumn<Event, String> pDateDebutColumn;
    @FXML private TableColumn<Event, String> pDateFinColumn;
    @FXML private TableColumn<Event, Integer> pNbSessionsColumn;
    @FXML private TableColumn<Event, String> pStatusColumn;
    @FXML private TableColumn<Event, Void> pActionColumn;
    @FXML private Button add_event;
    private final EventService eventService = new EventService();
    private final SessionService sessionService = new SessionService();
    private ObservableList<Event> allEvents;

    @FXML private Button toutesButton;
    @FXML private Button enAttenteButton;
    @FXML private Button accepteButton;
    @FXML private Button rejeteButton;

    @FXML
    private void initialize() {
        try{
        titleColumn.setCellValueFactory(new PropertyValueFactory<>("title"));
        descColumn.setCellValueFactory(new PropertyValueFactory<>("description"));
        dateDebutColumn.setCellValueFactory(cellData -> {
            Date date = cellData.getValue().getDate_debut();
            return new SimpleStringProperty(date != null ? date.toString() : "");
        });

        dateFinColumn.setCellValueFactory(cellData -> {
            Date date = cellData.getValue().getDate_fin();
            return new SimpleStringProperty(date != null ? date.toString() : "");
        });
            maxParticipantsColumn.setCellValueFactory(new PropertyValueFactory<>("max_participants"));
            nbSessionsColumn.setCellValueFactory(new PropertyValueFactory<>("sessionCount"));
            statusColumn.setCellValueFactory(new PropertyValueFactory<>("status"));
            statusColumn.setCellFactory(column -> new TableCell<Event, String>() {
                @Override
                protected void updateItem(String status, boolean empty) {
                    super.updateItem(status, empty);

                    if (empty || status == null) {
                        setText(null);
                        setStyle("");
                    } else {
                        setText(status);
                        switch (status) {
                            case "Acceptee":
                                setStyle("-fx-text-fill: #4CAF50; -fx-font-weight: bold;");
                                break;
                            case "EnAttente":
                                setStyle("-fx-text-fill: #FFC107; -fx-font-weight: bold;");
                                break;
                            case "Rejetee":
                                setStyle("-fx-text-fill: #F44336; -fx-font-weight: bold;");
                                break;
                            default:
                                setText("Début");  // Default status if none matches
                                setStyle("-fx-text-fill: #2196F3; -fx-font-weight: bold;");
                        }
                    }
                }
            });

        actionColumn.setCellFactory(column -> new TableCell<Event, Void>() {
            private final Button detailBtn = new Button("Détail");
            private final Button editBtn = new Button("Modifier");
            private final Button deleteBtn = new Button("Supprimer");
            private final HBox buttons = new HBox(5, detailBtn, editBtn, deleteBtn);

            {
                detailBtn.getStyleClass().add("action-button");
                editBtn.getStyleClass().add("action-button");
                deleteBtn.getStyleClass().add("action-button");
                deleteBtn.setStyle("-fx-text-fill: #fff; -fx-background-color: #F44336;");
                detailBtn.setStyle("-fx-text-fill: #fff; -fx-background-color: #90CAF9;");
                editBtn.setStyle("-fx-text-fill: #fff; -fx-background-color: #1E88E5;");

                // Add button actions
                detailBtn.setOnAction(event -> {
                    Event data = getTableView().getItems().get(getIndex());
                    try {
                        // Determine which view to load based on user role
                        String fxmlPath;
                        if (data.getOrgniser().getId() == UserSession.getInstance().getUser().getId()) {
                            fxmlPath = "/pi2425/swappy_javafx/events/detailEventOrgniser.fxml";
                        } else {
                            fxmlPath = "/pi2425/swappy_javafx/events/eventDetails.fxml";
                        }

                        // Load the appropriate FXML
                        FXMLLoader loader = new FXMLLoader(getClass().getResource(fxmlPath));
                        Parent root = loader.load();

                        // Get controller and set event data
                        EventDetails controller = loader.getController();
                        controller.setEventId(data.getId());
                        controller.setServices(new EventService(), new SessionService());
                        Stage stage = (Stage) ((Node) event.getSource()).getScene().getWindow();
                        stage.setScene(new Scene(root));
                        stage.setTitle("Détail de l'événement");
                        stage.show();
                    } catch (IOException ex) {
                        ex.printStackTrace();
                        showAlert("Navigation Error", "Failed to load event details view", Alert.AlertType.ERROR);
                    }
                });

                editBtn.setOnAction(event -> {
                    Event data = getTableView().getItems().get(getIndex());
                    try {
                        // Determine which view to load based on user role
                        String fxmlPath;
                        if (data.getOrgniser().getId() == UserSession.getInstance().getUser().getId()) {
                            fxmlPath = "/pi2425/swappy_javafx/events/editEvent.fxml";
                        } else {
                            fxmlPath = "/pi2425/swappy_javafx/events/index.fxml";
                        }

                        FXMLLoader loader = new FXMLLoader(getClass().getResource(fxmlPath));
                        Parent root = loader.load();

                        // Get controller and set event data
                        EditEventController controller = loader.getController();
                        controller.setEventId(data.getId());
                        controller.setServices(new EventService(), new SessionService());
                        Stage stage = (Stage) ((Node) event.getSource()).getScene().getWindow();
                        stage.setScene(new Scene(root));
                        stage.setTitle("modifier un événement");
                        stage.show();
                    } catch (IOException ex) {
                        ex.printStackTrace();
                        showAlert("Navigation Error", "Failed to load event details view", Alert.AlertType.ERROR);
                    }
                });

                deleteBtn.setOnAction(event -> {
                    Event eventToDelete = getTableView().getItems().get(getIndex());
                    confirmAndDeleteEvent(eventToDelete);
                });
            }

            @Override
            protected void updateItem(Void item, boolean empty) {
                super.updateItem(item, empty);
                if (empty || getIndex() >= getTableView().getItems().size()) {
                    setGraphic(null);
                    setStyle("-fx-background-color: transparent;");
                } else {
                    setGraphic(buttons);
                }
            }
        });

            actionColumn.setMinWidth(250);
            actionColumn.setPrefWidth(250);
            eventTableView.setFixedCellSize(40); // Adjust as needed
            loadEvents();
            setupFilterButtons();

        } catch (Exception e) {
            e.printStackTrace();
            showAlert("Error", "Failed to initialize: " + e.getMessage(), Alert.AlertType.ERROR);
        }
        /*****************************************************************************************************/
        try {
            // Existing first table configuration...

            // Configure second table (participations)
            pTitleColumn.setCellValueFactory(new PropertyValueFactory<>("title"));
            pDescColumn.setCellValueFactory(new PropertyValueFactory<>("description"));
            pDateDebutColumn.setCellValueFactory(cellData -> {
                Date date = cellData.getValue().getDate_debut();
                return new SimpleStringProperty(date != null ? date.toString() : "");
            });
            pDateFinColumn.setCellValueFactory(cellData -> {
                Date date = cellData.getValue().getDate_fin();
                return new SimpleStringProperty(date != null ? date.toString() : "");
            });
            pNbSessionsColumn.setCellValueFactory(new PropertyValueFactory<>("sessionCount"));
            pStatusColumn.setCellValueFactory(new PropertyValueFactory<>("status"));

            pStatusColumn.setCellFactory(column -> new TableCell<Event, String>() {
                @Override
                protected void updateItem(String status, boolean empty) {
                    super.updateItem(status, empty);
                    if (empty || status == null) {
                        setText(null);
                        setStyle("");
                    } else {
                        setText(status);
                        switch (status) {
                            case "Acceptee":
                                setStyle("-fx-text-fill: #4CAF50; -fx-font-weight: bold;");
                                break;
                            case "EnAttente":
                                setStyle("-fx-text-fill: #FFC107; -fx-font-weight: bold;");
                                break;
                            case "Rejetee":
                                setStyle("-fx-text-fill: #F44336; -fx-font-weight: bold;");
                                break;
                            default:
                                setText("Début");
                                setStyle("-fx-text-fill: #2196F3; -fx-font-weight: bold;");
                        }
                    }
                }
            });

            pActionColumn.setCellFactory(column -> new TableCell<Event, Void>() {
                private final Button detailBtn = new Button("Plan de l'évenement");
                private final HBox buttons = new HBox(5, detailBtn);

                {
                    detailBtn.getStyleClass().add("action-button");
                    detailBtn.setStyle("-fx-text-fill: #fff; -fx-background-color: #90CAF9;");

                    detailBtn.setOnAction(event -> {
                        Event data = getTableView().getItems().get(getIndex());
                        try{



                        // Load the appropriate FXML
                        FXMLLoader loader = new FXMLLoader(getClass().getResource("/pi2425/swappy_javafx/events/planEvent.fxml"));
                        Parent root = loader.load();


                        EventPlanController controller = loader.getController();
                        controller.setEventId(data.getId());
                        controller.setServices(new EventService(), new SessionService());
                        Stage stage = (Stage) ((Node) event.getSource()).getScene().getWindow();
                        stage.setScene(new Scene(root));
                        stage.setTitle("Plan de l'évenement");
                        stage.show();
                    } catch (IOException ex) {
                    ex.printStackTrace();
                    showAlert("Navigation Error", "Failed to load event planing view", Alert.AlertType.ERROR);
                }


                    });
                }

                @Override
                protected void updateItem(Void item, boolean empty) {
                    super.updateItem(item, empty);
                    if (empty || getIndex() >= getTableView().getItems().size()) {
                        setGraphic(null);
                        setStyle("-fx-background-color: transparent;");
                    } else {
                        setGraphic(buttons);
                    }
                }
            });

            loadEvents();
            loadParticipations(); // Load the user's participations
            setupFilterButtons();
        } catch (Exception e) {
            e.printStackTrace();
            showAlert("Error", "Failed to initialize: " + e.getMessage(), Alert.AlertType.ERROR);
        }


        /*********************************************************************************************/

    }

    private void loadEvents() throws SQLException {
        List<Event> events = eventService.getMesEvent();
        allEvents = FXCollections.observableArrayList(events);  // Initialize here
        eventTableView.setItems(allEvents);
        eventTableView.refresh();
    }

    private void loadParticipations() throws SQLException {
        List<Event> participations = eventService.getParticipations();
        ObservableList<Event> participationEvents = FXCollections.observableArrayList(participations);
        participationTableView.setItems(participationEvents);
        participationTableView.refresh();
    }

    public void navigateToAddEvent() throws IOException {
        try {

            NavigationUtils.navigateTo("/pi2425/swappy_javafx/events/new_event.fxml", "Ajouter Événement",add_event);
        } catch (Exception e) {
            e.printStackTrace();
            showAlert("Navigation Error", "Failed to load add event view", Alert.AlertType.ERROR);
        }
    }


    private void confirmAndDeleteEvent(Event event) {
        // Create confirmation dialog
        Alert confirmation = new Alert(Alert.AlertType.CONFIRMATION);
        confirmation.setTitle("Confirmer la suppression");
        confirmation.setHeaderText("Supprimer l'événement");
        confirmation.setContentText("Êtes-vous sûr de vouloir supprimer l'événement : " + event.getTitle() + "?");

        // Customize buttons
        ButtonType yesButton = new ButtonType("Oui", ButtonBar.ButtonData.YES);
        ButtonType noButton = new ButtonType("Non", ButtonBar.ButtonData.NO);
        confirmation.getButtonTypes().setAll(yesButton, noButton);

        // Show dialog and wait for response
        Optional<ButtonType> result = confirmation.showAndWait();

        if (result.isPresent() && result.get() == yesButton) {

                // Delete from database
                try {
                    // Delete from database - assuming supprimer throws exception on failure
                    eventService.supprimer(event);

                    // If we get here, deletion was successful
                    eventTableView.getItems().remove(event);
                    showAlert("Succès", "Événement supprimé avec succès", Alert.AlertType.INFORMATION);

                } catch (SQLException e) {
                    e.printStackTrace();
                    showAlert("Erreur", "Échec de la suppression: " + e.getMessage(), Alert.AlertType.ERROR);
                }

        }
    }


    private void setupFilterButtons() {

        toutesButton.setOnAction(e -> applyFilter(null));
        enAttenteButton.setOnAction(e -> applyFilter("EnAttente"));
        accepteButton.setOnAction(e -> applyFilter("Acceptee"));
        rejeteButton.setOnAction(e -> applyFilter("Rejetee"));
    }

    private void applyFilter(String status) {
        if (status == null) {
            eventTableView.setItems(allEvents);
        } else {
            ObservableList<Event> filtered = allEvents.stream()
                    .filter(event -> status.equals(event.getStatus()))
                    .collect(Collectors.toCollection(FXCollections::observableArrayList));
            eventTableView.setItems(filtered);
        }
        eventTableView.refresh();
    }


}