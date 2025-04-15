package pi2425.swappy_javafx.controllers;



import javafx.application.Platform;
import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.fxml.Initializable;
import javafx.geometry.Insets;
import javafx.geometry.Pos;
import javafx.scene.Node;
import javafx.scene.Parent;
import javafx.scene.Scene;
import javafx.scene.control.Alert;
import javafx.scene.control.Hyperlink;
import javafx.scene.control.Label;
import javafx.scene.image.Image;
import javafx.scene.image.ImageView;
import javafx.scene.layout.*;
import javafx.scene.paint.Color;
import javafx.scene.shape.SVGPath;
import javafx.scene.text.Text;
import javafx.scene.text.TextFlow;
import javafx.stage.Stage;
import pi2425.swappy_javafx.entities.Event;
import pi2425.swappy_javafx.entities.Session;
import pi2425.swappy_javafx.services.EventService;
import pi2425.swappy_javafx.services.SessionService;
import pi2425.swappy_javafx.utils.LoadExternalImage;


import java.io.File;
import java.io.IOException;
import java.net.URL;
import java.nio.file.Path;
import java.nio.file.Paths;
import java.sql.SQLException;
import java.text.SimpleDateFormat;
import java.time.format.DateTimeFormatter;
import java.util.Arrays;
import java.util.List;
import java.util.Locale;
import java.util.ResourceBundle;


public class EventDetails implements Initializable {

    @FXML private StackPane eventImage;
    @FXML private Label titleLabel;
    @FXML private Label statusBadge;
    @FXML private Label statusTag;
    @FXML private Label placesTag;
    @FXML private Label dateLabel;
    @FXML private Label organizerLabel;
    @FXML private Text descriptionText;
    @FXML private TextFlow descriptionTextFlow;
    @FXML private Hyperlink backButton;
    @FXML private VBox sessionsContainer;
    @FXML private FlowPane tagsContainer;


    protected EventService eventService;
    protected SessionService sessionService;
    protected int eventId;

    // Color values for different statuses
    private static final String STATUS_ACCEPTED_COLOR = "#16a34a";
    private static final String STATUS_REJECTED_COLOR = "#dc2626";
    private static final String STATUS_PENDING_COLOR = "#ca8a04";

    // Session border colors for the alternate styling
    private static final List<String> SESSION_BORDER_COLORS = Arrays.asList(
            "#3b82f6", // blue
            "#22c55e", // green
            "#a855f7", // purple
            "#eab308"  // yellow
    );

    public void setEventId(int eventId) {
        this.eventId = eventId;
        loadEventDetails();
    }

    public void setServices(EventService eventService, SessionService sessionService) {
        this.eventService = eventService;
        this.sessionService = sessionService;
    }

    @Override
    public void initialize(URL url, ResourceBundle resourceBundle) {

        if (eventService == null) {
            eventService = new EventService(); // Adjust according to your service initialization
        }

        if (sessionService == null) {
            sessionService = new SessionService(); // Adjust according to your service initialization
        }
    }

    private void loadEventDetails() {
        try {
            System.out.println("Loading event with ID: " + eventId); // Debug
            Event event = eventService.getById(eventId);
            if (event == null) {
                System.out.println("Event not found!"); // Debug
                return;
            }
            System.out.println("Loaded event: " + event.getTitle()); // Debug

            Platform.runLater(() -> {
                updateUI(event);
                loadEventSessions(event);
            });
        } catch (Exception e) {
            e.printStackTrace();
            Platform.runLater(() -> {
                // Show error message in UI
                titleLabel.setText("Error loading event");
            });
        }
    }

    protected void updateUI(Event event) {
        Platform.runLater(() -> {
            try {
                System.out.println("Updating UI with event: " + event.getTitle());

                // Set event image
                Image image = loadEventImage(event.getId());
                BackgroundImage bg = new BackgroundImage(
                        image,
                        BackgroundRepeat.NO_REPEAT,
                        BackgroundRepeat.NO_REPEAT,
                        BackgroundPosition.DEFAULT,
                        new BackgroundSize(
                                1.0, 1.0,  // Scale to 100% width and height
                                true, true, // As percentages
                                false,      // Don't contain
                                true        // Do cover
                        )
                );
                eventImage.setBackground(new Background(bg));
                //eventImage.setImage(image);
                // Set all event details
                titleLabel.setText(event.getTitle());
                descriptionText.setText(event.getDescription());
                // Format and set dates
                DateTimeFormatter dateFormatter = DateTimeFormatter.ofPattern("dd MMM, yyyy",Locale.FRENCH);
                String startDate = event.getDate_debut().toLocalDate().format(dateFormatter);
                String endDate = event.getDate_fin().toLocalDate().format(dateFormatter);
                dateLabel.setText(startDate + " - " + endDate);

                // Set organizer
                if (event.getOrgniser() != null) {
                    organizerLabel.setText("Organisé par: " +
                            event.getOrgniser().getNom() + " " +
                            event.getOrgniser().getPrenom());
                }

                // Set status and places

                placesTag.setText("encore " + event.getMax_participants() + " places disponibles");

                // Apply status styling
                String status = event.getStatus();
                if ("Acceptee".equals(status)) {
                    statusTag.setText("Accepté");
                    statusBadge.setText("Accepté");
                    setStatusStyles(statusTag, "#dcfce7", STATUS_ACCEPTED_COLOR);
                } else if ("Rejetee".equals(status)) {
                    statusTag.setText("Rejeté");
                    statusBadge.setText("Rejeté");
                    setStatusStyles(statusTag, "#fee2e2", STATUS_REJECTED_COLOR);
                } else {
                    statusTag.setText("En attente");
                    statusBadge.setText("En attente");
                    setStatusStyles(statusTag, "#fef9c3", STATUS_PENDING_COLOR);
                }

                // Force UI refresh
                titleLabel.requestLayout();
                descriptionTextFlow.requestLayout();

            } catch (Exception e) {
                e.printStackTrace();
                titleLabel.setText("Error updating UI");
            }
        });
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



    private void setStatusStyles(Label label, String bgColor, String textColor) {
        label.setStyle(
                "-fx-background-color: " + bgColor + "; " +
                        "-fx-text-fill: " + textColor + "; " +
                        "-fx-background-radius: 15; " +
                        "-fx-font-size: 12; " +
                        "-fx-font-weight: bold;"
        );
        label.setPadding(new Insets(5, 10, 5, 10));
    }

    protected void loadEventSessions(Event event) {
        try {
            List<Session> sessions = sessionService.getSessionByEventId(event.getId());

            sessionsContainer.getChildren().clear();
            if (sessions.isEmpty()) {
                // Add placeholder when no sessions exist
                Label noSessionsLabel = new Label("Aucune session disponible pour cet événement");
                noSessionsLabel.setStyle("-fx-text-fill: #6b7280; -fx-font-style: italic;");
                sessionsContainer.getChildren().add(noSessionsLabel);
                return;
            }
            int colorIndex = 0;
            for (Session session : sessions) {
                String borderColor = SESSION_BORDER_COLORS.get(colorIndex % SESSION_BORDER_COLORS.size());
                colorIndex++;

                VBox sessionBox = createSessionBox(session, borderColor);
                sessionsContainer.getChildren().add(sessionBox);
            }
        } catch (Exception e) {
            e.printStackTrace();
            // Show error message or handle the exception
        }
    }

    protected VBox createSessionBox(Session session, String borderColor) {
        VBox sessionBox = new VBox();
        sessionBox.setSpacing(5);
        sessionBox.setStyle(
                "-fx-border-width: 0 0 0 4; " +
                        "-fx-border-color: " + borderColor + "; " +
                        "-fx-padding: 5 0 5 15;"
        );

        // Session objective
        Label objectiveLabel = new Label(session.getObjective());
        objectiveLabel.setStyle("-fx-font-weight: bold; -fx-text-fill: #1f2937;");

        DateTimeFormatter dateFormatter = DateTimeFormatter.ofPattern("MMM dd, yyyy", Locale.FRENCH);
        DateTimeFormatter timeFormatter = DateTimeFormatter.ofPattern("h:mm a", Locale.FRENCH);

// Format the dates
        String startDate = session.getStart_hour().format(dateFormatter);
        String startTime = session.getStart_hour().format(timeFormatter);
        String endTime = session.getEnd_hour().format(timeFormatter);

// Create UI elements with the formatted dates
        Label sessionTime = new Label(String.format("%s from %s to %s",
                startDate, startTime, endTime));

// 2. Different SVG based on session type
        SVGPath sessionIcon = new SVGPath();
        sessionIcon.setFill(Color.WHITE);
        sessionIcon.setStroke(Color.WHITE);
        sessionIcon.setStyle("-fx-spacing: 5");


        if ("Présentiel".equalsIgnoreCase(session.getType_session())) {
            // In-person session icon (people icon)
            sessionIcon.setContent("M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z");

        } else {
            // Default video icon for online sessions
            sessionIcon.setContent("M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z");
        }

// Create type badge
        HBox typeBox = new HBox();
        typeBox.setAlignment(Pos.CENTER);
        typeBox.setStyle(
                "-fx-background-color: #374151; " +
                        "-fx-background-radius: 4; " +
                        "-fx-padding: 5 10 5 10; " +
                        "-fx-spacing: 5; " +
                        "-fx-alignment: center;"
        );

        Label typeLabel = new Label(session.getType_session());
        typeLabel.setTextFill(Color.WHITE);
        typeLabel.setStyle("-fx-font-weight: lighter;");

        typeBox.getChildren().addAll(sessionIcon, typeLabel);


        // Add all elements to the session box
        sessionBox.getChildren().addAll(objectiveLabel, sessionTime, typeBox);
        VBox.setMargin(typeBox, new Insets(5, 0, 0, 0));

        return sessionBox;
    }

    @FXML
    private void handleBackAction() {
        try {
            // Use absolute path with leading slash
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/pi2425/swappy_javafx/events/index.fxml"));
            Parent root = loader.load();

            Scene scene = backButton.getScene();
            Stage stage = (Stage) scene.getWindow();
            scene.setRoot(root);
        } catch (IOException e) {
            e.printStackTrace();
            // Show error message to user
            Alert alert = new Alert(Alert.AlertType.ERROR);
            alert.setTitle("Navigation Error");
            alert.setHeaderText("Failed to load index view");
            alert.setContentText("Could not find index.fxml at: /pi2425/swappy_javafx/events/index.fxml");
            alert.showAndWait();
        }
    }
}