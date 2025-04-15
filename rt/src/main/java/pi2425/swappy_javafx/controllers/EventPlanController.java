package pi2425.swappy_javafx.controllers;

import javafx.beans.property.SimpleStringProperty;
import javafx.collections.FXCollections;
import javafx.collections.ObservableList;
import javafx.fxml.FXML;
import javafx.fxml.Initializable;
import javafx.scene.control.*;
import javafx.scene.control.cell.PropertyValueFactory;
import javafx.scene.layout.HBox;
import pi2425.swappy_javafx.entities.Session;
import pi2425.swappy_javafx.services.EventService;
import pi2425.swappy_javafx.services.SessionService;
import pi2425.swappy_javafx.utils.NavigationUtils;

import java.net.URL;
import java.time.LocalDateTime;
import java.time.format.DateTimeFormatter;
import java.util.List;
import java.util.ResourceBundle;

import static pi2425.swappy_javafx.utils.NavigationUtils.showAlert;

public class EventPlanController implements Initializable {

    @FXML private TableView<Session> sessionTableView;
    @FXML private TableColumn<Session, String> objectiveColumn;
    @FXML private TableColumn<Session, String> dateDebutColumn;
    @FXML private TableColumn<Session, String> dateFinColumn;
    @FXML private TableColumn<Session, String> typeColumn;
    @FXML private TableColumn<Session, Void> actionColumn;

    private EventService eventService;
    private SessionService sessionService;
    private int eventId;

    public void setEventId(int eventId) {
        this.eventId = eventId;
        loadSessions();
    }

    public void setServices(EventService eventService, SessionService sessionService) {
        this.eventService = eventService;
        this.sessionService = sessionService;
    }

    @Override
    public void initialize(URL url, ResourceBundle resourceBundle) {
        configureTableColumns();

    }

    private void configureTableColumns() {
        objectiveColumn.setCellValueFactory(new PropertyValueFactory<>("objective"));

        dateDebutColumn.setCellValueFactory(cellData -> {
            LocalDateTime start = cellData.getValue().getStart_hour();
            return new SimpleStringProperty(formatDateTime(start));
        });

        dateFinColumn.setCellValueFactory(cellData -> {
            LocalDateTime end = cellData.getValue().getEnd_hour();
            return new SimpleStringProperty(formatDateTime(end));
        });

        typeColumn.setCellValueFactory(new PropertyValueFactory<>("type_session"));

        actionColumn.setCellFactory(column -> new TableCell<Session, Void>() {
            private final Hyperlink actionLink = new Hyperlink();
            private final Label statusLabel = new Label();
            private final HBox container = new HBox(5, actionLink, statusLabel);

            {
                container.setStyle("-fx-alignment: CENTER_LEFT;");
            }

            @Override
            protected void updateItem(Void item, boolean empty) {
                super.updateItem(item, empty);

                if (empty || getIndex() >= getTableView().getItems().size()) {
                    setGraphic(null);
                } else {
                    Session session = getTableView().getItems().get(getIndex());
                    updateActionCell(session);
                    setGraphic(container);
                }
            }

            private void updateActionCell(Session session) {
                LocalDateTime now = LocalDateTime.now();
                LocalDateTime start = session.getStart_hour();
                LocalDateTime end = session.getEnd_hour();

                actionLink.setVisible(false);
                statusLabel.setVisible(false);

                if (now.isBefore(start)) {
                    // Session hasn't started yet
                    statusLabel.setText("La session n'a pas encore commencé");
                    statusLabel.setStyle("-fx-text-fill: #FFC107;");
                    statusLabel.setVisible(true);
                } else if (now.isAfter(end)) {
                    // Session is over
                    statusLabel.setText("La session est terminée");
                    statusLabel.setStyle("-fx-text-fill: #F44336;");
                    statusLabel.setVisible(true);
                } else {
                    // Session is active now
                    actionLink.setVisible(true);

                    if ("En ligne".equals(session.getType_session())) {
                        actionLink.setText("Rejoindre la réunion");
                        actionLink.setOnAction(e -> joinOnlineMeeting(session));
                    } else {
                        actionLink.setText("Voir la localisation");
                        actionLink.setOnAction(e -> showLocation(session));
                    }
                }
            }
        });
    }

    private String formatDateTime(LocalDateTime dateTime) {
        if (dateTime == null) return "";
        return dateTime.format(DateTimeFormatter.ofPattern("dd/MM/yyyy HH:mm"));
    }

    private void loadSessions() {
        try {
            if (sessionService == null) {
                sessionService = new SessionService();
            }

            System.out.println("Loading sessions for event ID: " + eventId); // Debug print
            List<Session> sessions = sessionService.getSessionByEventId(eventId);
            System.out.println("Number of sessions found: " + sessions.size()); // Debug print

            ObservableList<Session> sessionList = FXCollections.observableArrayList(sessions);
            sessionTableView.setItems(sessionList);
        } catch (Exception e) {
            System.err.println("Error loading sessions: " + e.getMessage()); // More visible error
            e.printStackTrace();
            showAlert("Erreur", "Impossible de charger les sessions: " + e.getMessage(), Alert.AlertType.ERROR);
        }
    }

    private void joinOnlineMeeting(Session session) {
        // Implement your online meeting joining logic here
        // For example, open a URL or launch a meeting application
        showAlert("Information", "Fonctionnalité de réunion en ligne à implémenter", Alert.AlertType.INFORMATION);
    }

    private void showLocation(Session session) {
        // Implement your location showing logic here
        // For example, open a map with the location
        showAlert("Information", "Fonctionnalité de localisation à implémenter", Alert.AlertType.INFORMATION);
    }
}