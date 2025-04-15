package pi2425.swappy_javafx.controllers;

import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.scene.Node;
import javafx.scene.Parent;
import javafx.scene.Scene;
import javafx.scene.control.*;
import javafx.scene.layout.HBox;
import javafx.scene.layout.VBox;
import javafx.scene.paint.Color;
import javafx.scene.shape.SVGPath;
import javafx.stage.Stage;
import pi2425.swappy_javafx.entities.Event;
import pi2425.swappy_javafx.entities.Session;
import pi2425.swappy_javafx.entities.User;
import pi2425.swappy_javafx.entities.UserSession;
import pi2425.swappy_javafx.services.EventService;
import pi2425.swappy_javafx.services.SessionService;
import pi2425.swappy_javafx.utils.Config;

import java.io.IOException;
import java.net.URI;
import java.net.http.HttpClient;
import java.net.http.HttpRequest;
import java.net.http.HttpResponse;
import java.sql.SQLException;
import java.util.Optional;

import static pi2425.swappy_javafx.utils.NavigationUtils.navigateToWithController;
import static pi2425.swappy_javafx.utils.NavigationUtils.showAlert;

public class MyEventDetailController extends EventDetails {
    @FXML private Hyperlink editButton;
    @FXML private Hyperlink deleteButton;
    @FXML private HBox actionsContainer;

    @Override
    protected void updateUI(Event event) {
        super.updateUI(event);
        User user = UserSession.getInstance().getUser();
        if (user != null) {
            if (user.getId() == event.getOrgniser().getId()) {
                addActionButtons();
            } else {
                if (actionsContainer != null) actionsContainer.setVisible(false);
            }
        }
    }

    private void addActionButtons() {
        editButton.setOnAction(e -> {
            try {
                FXMLLoader loader = new FXMLLoader(getClass().getResource("/pi2425/swappy_javafx/events/editEvent.fxml"));
                Parent root = loader.load();
                EditEventController controller = loader.getController();
                controller.setEventId(this.eventId);
                controller.setServices(this.eventService, this.sessionService);
                Stage stage = (Stage) ((Node) e.getSource()).getScene().getWindow();
                stage.setScene(new Scene(root));
                stage.setTitle("Modifier un événement");
                stage.show();
            } catch (IOException ex) {
                ex.printStackTrace();
                showAlert("Navigation Error", "Failed to load edit event view", Alert.AlertType.ERROR);
            }
        });

        deleteButton.setOnAction(e -> {
            try {
                handleDelete(eventService.getById(eventId));
            } catch (SQLException ex) {
                throw new RuntimeException(ex);
            }
        });
        editButton.setVisible(true);
        deleteButton.setVisible(true);
    }

    @Override
    protected VBox createSessionBox(Session session, String borderColor) {
        VBox sessionBox = super.createSessionBox(session, borderColor);
        User user = UserSession.getInstance().getUser();

        if (session.getEvent() != null && user != null && user.getId() == session.getEvent().getOrgniser().getId()) {
            HBox buttonContainer = new HBox(5);
            buttonContainer.setStyle("-fx-padding: 5 0 0 0; -fx-alignment: CENTER_LEFT;");

            if ("Présentiel".equalsIgnoreCase(session.getType_session())) {
                // Location button
                Button locationBtn = createActionButton(
                        "Envoyer Localisation",
                        "#3b82f6",
                        "M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z",
                        e -> sendLocation(session)
                );

                // Attendance button
                Button attendanceBtn = createActionButton(
                        "Vérifier Présence",
                        "#10b981",
                        "M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z",
                        e -> checkAttendance(session)
                );

                buttonContainer.getChildren().addAll(locationBtn, attendanceBtn);
            } else {
                // Online session button - CORRECTED VERSION
                Button startBtn = createActionButton(
                        "Lancer Session",
                        "#8b5cf6",
                        "M8 5v14l11-7L8 5z",
                        e -> startSession(session, (Node)e.getSource())  // Pass the clicked button as source node
                );
                buttonContainer.getChildren().add(startBtn);
            }

            sessionBox.getChildren().add(buttonContainer);
        }
        return sessionBox;
    }

    private Button createActionButton(String text, String color, String svgPath, javafx.event.EventHandler<javafx.event.ActionEvent> handler) {
        Button btn = new Button(text);
        btn.setStyle("-fx-background-color: #374151;" +
                "-fx-text-fill: white; " +
                "-fx-background-radius: 4; " +
                "-fx-padding: 5 10 5 10; " +
                "-fx-font-size: 12px; " +
                "-fx-font-weight: bold; " +
                "-fx-content-display: left; " +
                "-fx-graphic-text-gap: 5; " +
                "-fx-cursor: hand;" +
                "-fx-min-width: 260;-fx-pref-width: 260;-fx-max-width: 260;");

        SVGPath icon = new SVGPath();
        icon.setContent(svgPath);
        icon.setFill(Color.WHITE);
        icon.setStroke(Color.WHITE);
        icon.setStyle("-fx-scale-x: 0.8; -fx-scale-y: 0.8;");
        btn.setGraphic(icon);

        btn.setOnAction(handler);

        btn.setOnMouseEntered(e -> btn.setStyle(btn.getStyle() + "-fx-effect: dropshadow(three-pass-box, rgba(0,0,0,0.2), 5, 0, 0, 1);"));
        btn.setOnMouseExited(e -> btn.setStyle(btn.getStyle().replace("-fx-effect: dropshadow(three-pass-box, rgba(0,0,0,0.2), 5, 0, 0, 1);", "")));

        return btn;
    }

    private void handleDelete(Event event) {
        Alert confirmation = new Alert(Alert.AlertType.CONFIRMATION);
        confirmation.setTitle("Confirmer la suppression");
        confirmation.setHeaderText("Supprimer l'événement");
        confirmation.setContentText("Êtes-vous sûr de vouloir supprimer l'événement : " + event.getTitle() + "?");

        ButtonType yesButton = new ButtonType("Oui", ButtonBar.ButtonData.YES);
        ButtonType noButton = new ButtonType("Non", ButtonBar.ButtonData.NO);
        confirmation.getButtonTypes().setAll(yesButton, noButton);

        Optional<ButtonType> result = confirmation.showAndWait();
        if (result.isPresent() && result.get() == yesButton) {
            try {
                eventService.supprimer(event);
                showAlert("Succès", "Événement supprimé avec succès", Alert.AlertType.INFORMATION);
            } catch (SQLException e) {
                showAlert("Erreur", "Échec de la suppression: " + e.getMessage(), Alert.AlertType.ERROR);
            }
        }
    }

    private void sendLocation(Session session) {
        System.out.println("Sending location for session: " + session.getId());
    }

    private void checkAttendance(Session session) {
        System.out.println("Checking attendance for session: " + session.getId());
    }

    public void startSession(Session session, Node sourceNode) {
        try {
            User currentUser = UserSession.getInstance().getUser();
            boolean isOrganizer = session.getEvent().getOrgniser().getId() == currentUser.getId();

            MeetController controller = navigateToWithController(
                    "/pi2425/swappy_javafx/events/meet.fxml",
                    "Online Meeting - " + session.getObjective(),
                    sourceNode
            );

            if (controller != null) {
                controller.initializeMeeting(
                        "session_" + session.getId(),
                        isOrganizer,
                        currentUser.getEmail(),
                        "21e4635c-7777-451f-aa8f-b9c66daa5253"
                );
            }
        } catch(Exception e) {
            e.printStackTrace();
            showAlert("Error", "Failed to start meeting", Alert.AlertType.ERROR);
        }
    }

    private String getVideoSdkApiKey() {
        return Config.getProperty("videosdk.api.key");
    }

    private void notifyMeetingStarted(Session session) {
        new Thread(() -> {
            try {
                HttpClient client = HttpClient.newHttpClient();
                HttpRequest request = HttpRequest.newBuilder()
                        .uri(URI.create("http://your-backend/api/session/" + session.getId() + "/start"))
                        .POST(HttpRequest.BodyPublishers.noBody())
                        .build();

                HttpResponse<String> response = client.send(request, HttpResponse.BodyHandlers.ofString());
                System.out.println("Meeting start notification response: " + response.statusCode());
            } catch (Exception e) {
                e.printStackTrace();
            }
        }).start();
    }
}