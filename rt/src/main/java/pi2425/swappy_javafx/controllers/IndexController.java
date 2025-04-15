package pi2425.swappy_javafx.controllers;

import javafx.animation.*;
import javafx.event.ActionEvent;
import javafx.event.EventHandler;
import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.fxml.Initializable;
import javafx.geometry.Insets;
import javafx.geometry.Pos;
import javafx.scene.Node;
import javafx.scene.Parent;
import javafx.scene.Scene;
import javafx.scene.control.*;
import javafx.scene.effect.DropShadow;
import javafx.scene.image.Image;
import javafx.scene.image.ImageView;
import javafx.scene.layout.*;
import javafx.scene.paint.Color;
import javafx.scene.shape.Circle;
import javafx.scene.shape.Rectangle;
import javafx.stage.Stage;
import javafx.util.Duration;
import pi2425.swappy_javafx.entities.Event;
import pi2425.swappy_javafx.entities.UserSession;
import pi2425.swappy_javafx.services.EventService;
import pi2425.swappy_javafx.services.SessionService;
import pi2425.swappy_javafx.utils.LoadExternalImage;
import pi2425.swappy_javafx.utils.NavigationUtils;

import java.io.File;
import java.io.IOException;
import java.net.URL;
import java.nio.file.Path;
import java.nio.file.Paths;
import java.sql.SQLException;
import java.time.DayOfWeek;
import java.time.LocalDate;
import java.util.List;
import java.util.ResourceBundle;
import java.util.stream.Collectors;

import static pi2425.swappy_javafx.utils.NavigationUtils.showAlert;

public class IndexController implements Initializable {


    @FXML private FlowPane eventsGrid;
    @FXML private HeaderController headerController;
    @FXML private Button userButton;

    // Toggle buttons for filtering
    @FXML private ToggleButton allFilterButton;
    @FXML private ToggleButton todayFilterButton;
    @FXML private ToggleButton weekFilterButton;

    @FXML
    private ImageView eventImageView;
    @FXML private ImageView thumbnail;

    private ToggleGroup filterGroup;
    private final EventService eventService = new EventService();
    @FXML
    private Rectangle heroOverlay;
    @FXML StackPane heroBanner;
    @Override
    public void initialize(URL location, ResourceBundle resources) {


        Image image1 = LoadExternalImage.loadExternalImage("assets/images/event.jpg");
        filterGroup = new ToggleGroup();

        // Verify buttons were injected
        if (allFilterButton == null || todayFilterButton == null ||
                weekFilterButton == null ) {
            throw new IllegalStateException("One or more ToggleButtons failed to inject");
        }

        // Add buttons to toggle group
        allFilterButton.setToggleGroup(filterGroup);
        todayFilterButton.setToggleGroup(filterGroup);
        weekFilterButton.setToggleGroup(filterGroup);


        // Select default filter
        filterGroup.selectToggle(allFilterButton);

        // Load events
        try {
            loadAcceptedEvents();
            heroBanner.setMaxWidth(Double.MAX_VALUE);
            heroBanner.setPrefWidth(Region.USE_COMPUTED_SIZE);
            BackgroundImage bg = new BackgroundImage(
                    image1,
                    BackgroundRepeat.NO_REPEAT,
                    BackgroundRepeat.NO_REPEAT,
                    BackgroundPosition.CENTER,
                    new BackgroundSize(
                            1.0, 1.0,  // Scale to 100% width and height
                            true, true, // As percentages
                            false,      // Don't contain
                            true        // Do cover
                    )
            );
            heroBanner.setBackground(new Background(bg));
            heroOverlay.widthProperty().bind(heroBanner.widthProperty());

        } catch (SQLException e) {
            e.printStackTrace();
            showErrorAlert("Database Error", "Failed to load events from database");
        }

        setupFilters();
        setupUserButton();
    }
    private void loadAcceptedEvents() throws SQLException {
        List<Event> events = eventService.getAllAcceptedEvents();

        if (events.isEmpty()) {
            showNoEventsMessage();
        } else {
            for (Event event : events) {
                eventsGrid.getChildren().add(createEventCard(event));
            }
        }
    }

    public Image loadEventImage(int eventId) throws SQLException {
        Event event = eventService.getById(eventId);
        if (event != null && event.getImage() != null) {
            Path imagePath = Paths.get(System.getProperty("user.dir"), "..", "swapify-dev", "public", "uploads", "images", event.getImage());
            String imageUri = imagePath.toUri().toString();
            Image image = new Image(imageUri);
            return image;
        }
        return null;
    }

    private VBox createEventCard(Event event) {
        VBox card = new VBox();
        card.getStyleClass().add("event-card");

        // Image section with rounded corners
        StackPane imageContainer = new StackPane();
        imageContainer.getStyleClass().add("event-image-container");

        ImageView imageView = new ImageView();
        try {
            Image image = loadEventImage(event.getId());

            if (image != null && !image.isError()) {
                imageView.setImage(image);
            } else {
                // Try fallback
                Image fallback = LoadExternalImage.loadExternalImage("assets/images/eventHero.jpg");
                if (fallback != null && !fallback.isError()) {
                    imageView.setImage(fallback);
                } else {
                    // Ultimate fallback - colored rectangle
                    Rectangle placeholder = new Rectangle(280, 160, Color.LIGHTGRAY);
                    placeholder.setArcWidth(24);  // Rounded corners for rectangle
                    placeholder.setArcHeight(24);
                    imageContainer.getChildren().add(placeholder);
                    System.out.println("Could not load any image for event: " + event.getTitle());
                }
            }
        } catch (Exception e) {
            // If everything fails, use placeholder with rounded corners
            Rectangle placeholder = new Rectangle(280, 160, Color.LIGHTGRAY);
            placeholder.setArcWidth(24);
            placeholder.setArcHeight(24);
            imageContainer.getChildren().add(placeholder);
            System.err.println("Error loading image for event: " + event.getTitle());
        }

        // Set image properties
        if (imageView.getImage() != null) {
            imageView.setFitWidth(280);
            imageView.setFitHeight(160);
            imageView.setPreserveRatio(false);

            // Create clip to round the corners of the image
            Rectangle clip = new Rectangle(280, 160);
            clip.setArcWidth(24);  // Matching the 12px radius in CSS (24 = 2*12)
            clip.setArcHeight(24);
            imageView.setClip(clip);

            StackPane.setMargin(imageView, new Insets(0));
            imageContainer.getChildren().add(0, imageView);
        }

        // Content section with improved spacing
        VBox content = new VBox(10);  // Increased spacing between elements
        content.getStyleClass().add("event-content");

        // Title and organizer with better styling
        VBox titleBox = new VBox(4);
        Label title = new Label(event.getTitle());
        title.getStyleClass().add("event-title");
        title.setWrapText(true);
        Label organizer = new Label("By: " + event.getOrgniser().getNom() + " " + event.getOrgniser().getPrenom());
        organizer.getStyleClass().add("event-organizer");
        titleBox.getChildren().addAll(title, organizer);

        // Date and participants with icons
        HBox detailsBox = new HBox(15);  // Increased spacing
        detailsBox.setAlignment(Pos.CENTER_LEFT);

        ImageView calendarIcon = new ImageView(new Image(getClass().getResourceAsStream("/pi2425/swappy_javafx/assets/icons/calendar.png")));
        calendarIcon.setFitWidth(16);  // Slightly larger icons
        calendarIcon.setFitHeight(16);

        Label date = new Label(formatEventDate(event));
        date.getStyleClass().add("event-detail");

        ImageView participantsIcon = new ImageView(new Image(getClass().getResourceAsStream("/pi2425/swappy_javafx/assets/icons/participants.png")));
        participantsIcon.setFitWidth(16);
        participantsIcon.setFitHeight(16);

        Label participants = new Label(event.getMax_participants() + " spots");
        participants.getStyleClass().add("event-detail");

        detailsBox.getChildren().addAll(calendarIcon, date, participantsIcon, participants);

        // Description with better styling
        Label description = new Label(shortenDescription(event.getDescription()));
        description.getStyleClass().add("event-description");
        description.setWrapText(true);

        // Action buttons with improved styling
        HBox buttonsBox = new HBox(15);  // Increased spacing
        buttonsBox.setAlignment(Pos.CENTER_LEFT);
        buttonsBox.setPadding(new Insets(5, 0, 0, 0));  // Add padding at top

        Button detailsButton = new Button("Details");
        detailsButton.getStyleClass().add("details-button");
        detailsButton.setOnAction(navigateToDetail(event));
        Button registerButton = new Button("Réservez ta place");
        registerButton.getStyleClass().add("register-button");


        registerButton.setOnAction(e -> {
            if (event.getMax_participants() <= 0) {
                registerButton.setText("Complet");
                registerButton.setDisable(true);
                registerButton.getStyleClass().add("disabled-button");
            } else {
                if(participerEvent(event)){
                    registerButton.setText("Vérifier planning");
                    registerButton.getStyleClass().remove("register-button");
                    registerButton.getStyleClass().add("check-planning-button");
                    registerButton.setOnAction(ev -> checkPlanning(event));
                    participants.setText((event.getMax_participants()) + " spots");
                };

                // Change button after registration


            }
        });

        buttonsBox.getChildren().addAll(detailsButton, registerButton);



        // Assemble card
        content.getChildren().addAll(titleBox, detailsBox, description, buttonsBox);
        card.getChildren().addAll(imageContainer, content);

        return card;
    }

    private void checkPlanning(Event event) {

    }


    private String formatEventDate(Event event) {
        // Format the date range nicely
        return event.getDate_debut().toString(); // Simple version - improve with DateTimeFormatter
    }

    private String shortenDescription(String description) {
        if (description == null) return "";
        if (description.length() > 100) {
            return description.substring(0, 100) + "...";
        }
        return description;
    }



    private void setupFilters() {
        filterGroup.selectedToggleProperty().addListener((obs, oldVal, newVal) -> {
            if (newVal != null) {
                try {
                    eventsGrid.getChildren().clear();
                    ToggleButton selectedButton = (ToggleButton) newVal;
                    String filter = selectedButton.getText();

                    List<Event> filteredEvents;
                    switch (filter) {
                        case "Today":
                            filteredEvents = getTodayEvents();
                            break;
                        case "This Week":
                            filteredEvents = getThisWeekEvents();
                            break;
                        default:
                            filteredEvents = eventService.getAllAcceptedEvents();
                    }

                    if (filteredEvents.isEmpty()) {
                        showNoEventsMessage();
                    } else {
                        for (Event event : filteredEvents) {
                            eventsGrid.getChildren().add(createEventCard(event));
                        }
                    }
                } catch (SQLException e) {
                    e.printStackTrace();
                    showErrorAlert("Database Error", "Failed to filter events");
                }
            }
        });
    }

    public List<Event> getTodayEvents() throws SQLException {
        LocalDate today = LocalDate.now();
        List<Event> allEvents = eventService.getAllAcceptedEvents();

        return allEvents.stream()
                .filter(event -> event.getDate_debut().toLocalDate().equals(today))
                .collect(Collectors.toList());
    }

    public List<Event> getThisWeekEvents() throws SQLException {
        LocalDate today = LocalDate.now();
        LocalDate startOfWeek = today.with(DayOfWeek.MONDAY);
        LocalDate endOfWeek = today.with(DayOfWeek.SUNDAY);

        List<Event> allEvents = eventService.getAllAcceptedEvents();

        return allEvents.stream()
                .filter(event -> {
                    LocalDate eventDate = event.getDate_debut().toLocalDate();
                    return !eventDate.isBefore(startOfWeek) && !eventDate.isAfter(endOfWeek);
                })
                .collect(Collectors.toList());
    }

    private void showNoEventsMessage() {
        eventsGrid.getChildren().clear();
        Label noEventsLabel = new Label("No events available");
        noEventsLabel.getStyleClass().add("no-events-label");
        eventsGrid.getChildren().add(noEventsLabel);
    }

    private void showErrorAlert(String title, String message) {
        Alert alert = new Alert(Alert.AlertType.ERROR);
        alert.setTitle(title);
        alert.setHeaderText(null);
        alert.setContentText(message);
        alert.showAndWait();
    }

    private void setupUserButton() {
        System.out.println("usr");
    }


    private EventHandler<ActionEvent> navigateToDetail(Event event) {
        return e -> {
            try {
                // Load the FXML
                FXMLLoader loader = new FXMLLoader(getClass().getResource("/pi2425/swappy_javafx/events/eventDetails.fxml"));
                Parent root = loader.load();
                EventDetails controller = loader.getController();
                controller.setEventId(event.getId());
                controller.setServices(new EventService(), new SessionService());
                Stage stage = (Stage) ((Node) e.getSource()).getScene().getWindow();
                Scene scene = new Scene(root);
                stage.setScene(scene);
                stage.setTitle("Détail de l'événement");
                stage.show();
            } catch (IOException ex) {
                ex.printStackTrace();
                showAlert("Navigation Error", "Failed to load event details view", Alert.AlertType.ERROR);
            }
        };
    }


    public boolean participerEvent(Event event) {
        if (UserSession.getInstance() == null || UserSession.getInstance().getUser() == null) {
            showErrorAlert("Authentification requise", "Veuillez vous connecter pour participer à un événement");
            return false;
        }

        // Check if event has available spots
        if (event.getMax_participants() <= 0) {
            showErrorAlert("Complet", "Désolé, cet événement n'a plus de places disponibles");
            return false;
        }

        try {


            // Check for conflicting events
            if (eventService.hasConflictingEvents(UserSession.getInstance().getUser().getId(), event)) {
                showErrorAlert("Conflit d'horaire", "Vous avez déjà un événement à ce moment-là");
                return false;
            }


            event.setMax_participants(event.getMax_participants() - 1);
            eventService.participerEvent(event);
            showAlert("Succès", "Inscription confirmée!", Alert.AlertType.INFORMATION);
            return true;

        } catch (SQLException e) {
            showErrorAlert("Erreur", "Échec de l'inscription: " + e.getMessage());
        }
        return false;
    }

}