package pi2425.swappy_javafx.controllers;

import javafx.fxml.FXML;
import javafx.scene.Node;
import javafx.scene.control.*;
import javafx.scene.layout.*;
import javafx.stage.Stage;
import pi2425.swappy_javafx.entities.Etat;
import pi2425.swappy_javafx.entities.Event;
import pi2425.swappy_javafx.entities.Session;
import pi2425.swappy_javafx.services.EventService;
import pi2425.swappy_javafx.services.SessionService;
import pi2425.swappy_javafx.tests.HelloApplication;
import pi2425.swappy_javafx.utils.NavigationUtils;
import pi2425.swappy_javafx.utils.UploadImageUtil;
import pi2425.swappy_javafx.utils.ValidationUtils;

import java.io.IOException;
import java.nio.file.Path;
import java.nio.file.Paths;
import java.sql.SQLException;
import java.time.LocalDate;
import java.time.LocalDateTime;
import java.time.LocalTime;
import java.util.Optional;
import java.util.stream.Collectors;
import java.util.stream.IntStream;

import static pi2425.swappy_javafx.utils.NavigationUtils.showAlert;

public class AddEventController {
    @FXML
    private VBox sessionsContainer;
    @FXML
    private Button addSessionButton;
    @FXML
    private Button removeSessionButton;
    @FXML
    private Button backButton;

    @FXML
    private TextField titreEvent;

    @FXML
    private TextArea descriptionEvent;
    @FXML
    private DatePicker dateDebut;
    @FXML
    private DatePicker dateFin;

    @FXML
    private TextField nbParticipantEvent;

    @FXML
    private Button uploadImageButton;

    @FXML
    private Label imageLabel;

    @FXML
    private Button cancelAdd;
    @FXML
    private Button saveEventButton;

    @FXML private Label titleError;
    @FXML private Label descriptionError;
    @FXML private Label dateDebutError;
    @FXML private Label dateFinError;
    @FXML private Label participantsError;
    @FXML private Label imageError;

    private String uploadedImageName = null;
    Path imagePath = Paths.get(System.getProperty("user.dir"), "..", "swapify-dev", "public", "uploads", "images");

    private final EventService eventService = new EventService();
    private final SessionService sessionService = new SessionService();

    @FXML
    private void handleImageUpload() {
        Stage stage = (Stage) imageLabel.getScene().getWindow();

        uploadedImageName = UploadImageUtil.uploadImage(stage, imagePath.toString());
        if (uploadedImageName != null) {
            imageLabel.setText(uploadedImageName);
        } else {
            imageLabel.setText("aucune fichier sélectionné");
        }
    }


    private boolean validateForm() {
        boolean isValid = true;

        // Title validation
        if (titreEvent.getText().trim().isEmpty()) {
            ValidationUtils.showError(titleError, "Le titre de l'événement est obligatoire.");
            isValid = false;
        } else {
            ValidationUtils.clearError(titleError);
        }

        // Description validation
        String description = descriptionEvent.getText().trim();
        if (description.isEmpty()) {
            ValidationUtils.showError(descriptionError, "La description est obligatoire.");
            isValid = false;
        } else if (description.length() < 10) {
            ValidationUtils.showError(descriptionError, "La description doit contenir au moins 10 caractères.");
            isValid = false;
        } else if (description.length() > 500) {
            ValidationUtils.showError(descriptionError, "La description ne peut pas dépasser 500 caractères.");
            isValid = false;
        } else {
            ValidationUtils.clearError(descriptionError);
        }

        // Date validation
        LocalDate today = LocalDate.now();
        LocalDate debut = dateDebut.getValue();
        LocalDate fin = dateFin.getValue();

        if (debut == null) {
            ValidationUtils.showError(dateDebutError, "La date de début est obligatoire.");
            isValid = false;
        } else if (debut.isBefore(today)) {
            ValidationUtils.showError(dateDebutError, "La date de début doit être aujourd'hui ou dans le futur.");
            isValid = false;
        } else {
            ValidationUtils.clearError(dateDebutError);
        }

        if (fin == null) {
            ValidationUtils.showError(dateFinError, "La date de fin est obligatoire.");
            isValid = false;
        } else if (debut != null && fin.isBefore(debut)) {
            ValidationUtils.showError(dateFinError, "La date de fin doit être postérieure à la date de début.");
            isValid = false;
        } else {
            ValidationUtils.clearError(dateFinError);
        }

        // Participants validation
        try {
            int participants = Integer.parseInt(nbParticipantEvent.getText().trim());
            if (participants <= 0) {
                ValidationUtils.showError(participantsError, "Le nombre maximum de participants doit être un nombre positif.");
                isValid = false;
            } else {
                ValidationUtils.clearError(participantsError);
            }
        } catch (NumberFormatException e) {
            ValidationUtils.showError(participantsError, "Le nombre de participant est obligatoire.");
            isValid = false;
        }
        if (sessionsContainer.getChildren().stream()
                .filter(node -> node instanceof VBox)
                .count() == 0) {
            showAlert("Error", "Au moins une session est requise.", Alert.AlertType.ERROR);
            isValid = false;
        }



        return isValid;
    }


    public void addSessionToEvent() {
        // Create main VBox for a session
        VBox sessionBox = new VBox(10);
        sessionBox.setStyle("-fx-border-color: #E0E0E0; -fx-border-style: solid; -fx-border-width: 1; -fx-border-radius: 2; -fx-padding: 16; -fx-spacing: 10;");

        // Session Objective
        VBox objectiveBox = new VBox(4);
        Label objectiveLabel = new Label("Objectif de la session *");
        objectiveLabel.setStyle("-fx-text-fill: #323130; -fx-font-weight: bold;");

        TextField sessionName = new TextField();
        sessionName.setPromptText("Titre de la session");
        sessionName.setPrefHeight(32);
        sessionName.setMaxWidth(Double.MAX_VALUE);
        sessionName.setStyle("-fx-background-radius: 2; -fx-border-radius: 2; -fx-border-color: #8A8886; -fx-padding: 8; -fx-font-size: 14;");

        Label objectiveError = new Label();
        objectiveError.setStyle("-fx-text-fill: #a4262c; -fx-font-size: 12;");
        objectiveError.setVisible(false);

        objectiveBox.getChildren().addAll(objectiveLabel, sessionName, objectiveError);

        // Start and End DateTime (50/50 split)
        GridPane timeGrid = new GridPane();
        timeGrid.setHgap(16);
        timeGrid.setVgap(4);

        // Column constraints for 50/50 split
        ColumnConstraints col1 = new ColumnConstraints();
        col1.setPercentWidth(50);
        ColumnConstraints col2 = new ColumnConstraints();
        col2.setPercentWidth(50);
        timeGrid.getColumnConstraints().addAll(col1, col2);

        // Row constraints - one for label, one for date picker, one for time picker, one for error
        RowConstraints labelRow = new RowConstraints();
        RowConstraints dateRow = new RowConstraints();
        RowConstraints timeRow = new RowConstraints();
        RowConstraints errorRow = new RowConstraints();
        errorRow.setPrefHeight(20); // Give some space for error message
        timeGrid.getRowConstraints().addAll(labelRow, dateRow, timeRow, errorRow);

        // Start date/time components
        Label startLabel = new Label("Date/Heure début *");
        startLabel.setStyle("-fx-text-fill: #323130; -fx-font-weight: bold;");

        // Date picker for start date
        DatePicker startDatePicker = new DatePicker();
        startDatePicker.setPromptText("Date de début");
        startDatePicker.setPrefHeight(32);
        startDatePicker.setMaxWidth(Double.MAX_VALUE);
        startDatePicker.setStyle("-fx-background-radius: 2; -fx-border-radius: 2; -fx-border-color: #8A8886; -fx-font-size: 14;");
        // Initialize with the event start date
        startDatePicker.setValue(dateDebut.getValue());

        // Time picker for start time
        HBox startTimePicker = new HBox(5);
        ComboBox<String> startHourCombo = new ComboBox<>();
        startHourCombo.getItems().addAll(
                IntStream.rangeClosed(0, 23)
                        .mapToObj(i -> String.format("%02d", i))
                        .collect(Collectors.toList()));
        startHourCombo.setPromptText("HH");
        startHourCombo.setPrefWidth(80);
        startHourCombo.setStyle("-fx-background-radius: 2; -fx-border-radius: 2; -fx-border-color: #8A8886; -fx-font-size: 14;");

        ComboBox<String> startMinuteCombo = new ComboBox<>();
        startMinuteCombo.getItems().addAll(
                IntStream.rangeClosed(0, 59)
                        .mapToObj(i -> String.format("%02d", i))
                        .collect(Collectors.toList()));
        startMinuteCombo.setPromptText("MM");
        startMinuteCombo.setPrefWidth(80);
        startMinuteCombo.setStyle("-fx-background-radius: 2; -fx-border-radius: 2; -fx-border-color: #8A8886; -fx-font-size: 14;");

        Label startSeparator = new Label(":");
        startSeparator.setStyle("-fx-font-size: 14; -fx-alignment: center;");

        startTimePicker.getChildren().addAll(startHourCombo, startSeparator, startMinuteCombo);

        Label startTimeError = new Label();
        startTimeError.setStyle("-fx-text-fill: #a4262c; -fx-font-size: 12;");
        startTimeError.setVisible(false);

        // End date/time components
        Label endLabel = new Label("Date/Heure fin *");
        endLabel.setStyle("-fx-text-fill: #323130; -fx-font-weight: bold;");

        // Date picker for end date
        DatePicker endDatePicker = new DatePicker();
        endDatePicker.setPromptText("Date de fin");
        endDatePicker.setPrefHeight(32);
        endDatePicker.setMaxWidth(Double.MAX_VALUE);
        endDatePicker.setStyle("-fx-background-radius: 2; -fx-border-radius: 2; -fx-border-color: #8A8886; -fx-font-size: 14;");
        // Initialize with the event start date
        endDatePicker.setValue(dateDebut.getValue());

        // Time picker for end time
        HBox endTimePicker = new HBox(5);
        ComboBox<String> endHourCombo = new ComboBox<>();
        endHourCombo.getItems().addAll(
                IntStream.rangeClosed(0, 23)
                        .mapToObj(i -> String.format("%02d", i))
                        .collect(Collectors.toList()));
        endHourCombo.setPromptText("HH");
        endHourCombo.setPrefWidth(80);
        endHourCombo.setStyle("-fx-background-radius: 2; -fx-border-radius: 2; -fx-border-color: #8A8886; -fx-font-size: 14;");

        ComboBox<String> endMinuteCombo = new ComboBox<>();
        endMinuteCombo.getItems().addAll(
                IntStream.rangeClosed(0, 59)
                        .mapToObj(i -> String.format("%02d", i))
                        .collect(Collectors.toList()));
        endMinuteCombo.setPromptText("MM");
        endMinuteCombo.setPrefWidth(80);
        endMinuteCombo.setStyle("-fx-background-radius: 2; -fx-border-radius: 2; -fx-border-color: #8A8886; -fx-font-size: 14;");

        Label endSeparator = new Label(":");
        endSeparator.setStyle("-fx-font-size: 14; -fx-alignment: center;");

        endTimePicker.getChildren().addAll(endHourCombo, endSeparator, endMinuteCombo);

        Label endTimeError = new Label();
        endTimeError.setStyle("-fx-text-fill: #a4262c; -fx-font-size: 12;");
        endTimeError.setVisible(false);

        // Add components to grid
        timeGrid.add(startLabel, 0, 0);
        timeGrid.add(endLabel, 1, 0);
        timeGrid.add(startDatePicker, 0, 1);
        timeGrid.add(endDatePicker, 1, 1);
        timeGrid.add(startTimePicker, 0, 2);
        timeGrid.add(endTimePicker, 1, 2);
        timeGrid.add(startTimeError, 0, 3);
        timeGrid.add(endTimeError, 1, 3);

        // Session Type
        VBox typeBox = new VBox(4);
        Label typeLabel = new Label("Type de session *");
        typeLabel.setStyle("-fx-text-fill: #323130; -fx-font-weight: bold;");

        ComboBox<String> sessionType = new ComboBox<>();
        sessionType.getItems().addAll("En ligne", "Présentiel");
        sessionType.setPromptText("Veuillez sélectionner un type pour cette session");
        sessionType.setPrefHeight(32);
        sessionType.setMaxWidth(Double.MAX_VALUE);
        sessionType.setStyle("-fx-background-radius: 2; -fx-border-radius: 2; -fx-border-color: #8A8886; -fx-font-size: 14;");

        Label typeError = new Label();
        typeError.setStyle("-fx-text-fill: #a4262c; -fx-font-size: 12;");
        typeError.setVisible(false);

        typeBox.getChildren().addAll(typeLabel, sessionType, typeError);

        // Remove Button
        Button removeButton = new Button("Supprimer");
        removeButton.setPrefHeight(32);
        removeButton.setMaxWidth(Double.MAX_VALUE);
        removeButton.setStyle("-fx-background-color: #c13c37; -fx-text-fill: white; -fx-background-radius: 2; -fx-font-size: 14; -fx-alignment: center;");
        removeButton.setOnAction(event -> sessionsContainer.getChildren().remove(sessionBox));

        // Add all components to the session box
        sessionBox.getChildren().addAll(objectiveBox, timeGrid, typeBox, removeButton);

        // Add to container (before the Add Session button)
        int lastIndex = sessionsContainer.getChildren().size();
        if (lastIndex > 0 && sessionsContainer.getChildren().get(lastIndex - 1) == addSessionButton) {
            sessionsContainer.getChildren().add(lastIndex - 1, sessionBox);
        } else {
            sessionsContainer.getChildren().add(sessionBox);
        }

        // Add validation listeners
        // When start date changes, validate that it's within event date range
        startDatePicker.valueProperty().addListener((obs, oldVal, newVal) -> {
            if (newVal != null) {
                LocalDate eventStart = dateDebut.getValue();
                LocalDate eventEnd = dateFin.getValue();
                if (eventStart != null && eventEnd != null) {
                    if (newVal.isBefore(eventStart) || newVal.isAfter(eventEnd)) {
                        startTimeError.setText("La date doit être comprise entre le début et la fin de l'événement.");
                        startTimeError.setVisible(true);
                    } else {
                        startTimeError.setVisible(false);
                    }
                }
            }
        });

        // When end date changes, validate that it's within event date range and after/equal to start date
        endDatePicker.valueProperty().addListener((obs, oldVal, newVal) -> {
            if (newVal != null) {
                LocalDate eventStart = dateDebut.getValue();
                LocalDate eventEnd = dateFin.getValue();
                LocalDate sessionStart = startDatePicker.getValue();

                if (eventStart != null && eventEnd != null) {
                    if (newVal.isBefore(eventStart) || newVal.isAfter(eventEnd)) {
                        endTimeError.setText("La date doit être comprise entre le début et la fin de l'événement.");
                        endTimeError.setVisible(true);
                    } else if (sessionStart != null && newVal.isBefore(sessionStart)) {
                        endTimeError.setText("La date de fin doit être égale ou postérieure à la date de début de session.");
                        endTimeError.setVisible(true);
                    } else {
                        endTimeError.setVisible(false);
                    }
                }
            }
        });
    }
    private boolean validateSession(VBox sessionBox) {
        boolean isValid = true;

        // Get all components from the session box
        VBox objectiveBox = (VBox) sessionBox.getChildren().get(0);
        TextField objectiveField = (TextField) objectiveBox.getChildren().get(1);
        Label objectiveError = (Label) objectiveBox.getChildren().get(2);

        GridPane timeGrid = (GridPane) sessionBox.getChildren().get(1);

        // Get date pickers
        DatePicker startDatePicker = (DatePicker) timeGrid.getChildren().get(2);
        DatePicker endDatePicker = (DatePicker) timeGrid.getChildren().get(3);

        // Get time pickers
        HBox startTimePicker = (HBox) timeGrid.getChildren().get(4);
        HBox endTimePicker = (HBox) timeGrid.getChildren().get(5);

        // Get error labels
        Label startTimeError = (Label) timeGrid.getChildren().get(6);
        Label endTimeError = (Label) timeGrid.getChildren().get(7);

        VBox typeBox = (VBox) sessionBox.getChildren().get(2);
        ComboBox<String> typeField = (ComboBox<String>) typeBox.getChildren().get(1);
        Label typeError = (Label) typeBox.getChildren().get(2);

        // Get time components
        ComboBox<String> startHour = (ComboBox<String>) startTimePicker.getChildren().get(0);
        ComboBox<String> startMinute = (ComboBox<String>) startTimePicker.getChildren().get(2);
        ComboBox<String> endHour = (ComboBox<String>) endTimePicker.getChildren().get(0);
        ComboBox<String> endMinute = (ComboBox<String>) endTimePicker.getChildren().get(2);

        // Validate objective
        if (objectiveField.getText().trim().isEmpty()) {
            objectiveError.setText("L'objectif de la session est obligatoire.");
            objectiveError.setVisible(true);
            isValid = false;
        } else {
            objectiveError.setVisible(false);
        }

        // Validate dates
        LocalDate eventStartDate = dateDebut.getValue();
        LocalDate eventEndDate = dateFin.getValue();
        LocalDate sessionStartDate = startDatePicker.getValue();
        LocalDate sessionEndDate = endDatePicker.getValue();

        if (sessionStartDate == null) {
            startTimeError.setText("La date de début est obligatoire.");
            startTimeError.setVisible(true);
            isValid = false;
        } else if (sessionStartDate.isBefore(eventStartDate) || sessionStartDate.isAfter(eventEndDate)) {
            startTimeError.setText("La date doit être comprise entre le début et la fin de l'événement.");
            startTimeError.setVisible(true);
            isValid = false;
        } else {
            startTimeError.setVisible(false);
        }

        if (sessionEndDate == null) {
            endTimeError.setText("La date de fin est obligatoire.");
            endTimeError.setVisible(true);
            isValid = false;
        } else if (sessionEndDate.isBefore(eventStartDate) || sessionEndDate.isAfter(eventEndDate)) {
            endTimeError.setText("La date doit être comprise entre le début et la fin de l'événement.");
            endTimeError.setVisible(true);
            isValid = false;
        } else if (sessionStartDate != null && sessionEndDate.isBefore(sessionStartDate)) {
            endTimeError.setText("La date de fin doit être égale ou postérieure à la date de début de session.");
            endTimeError.setVisible(true);
            isValid = false;
        } else {
            endTimeError.setVisible(false);
        }

        // Validate start time
        if (startHour.getValue() == null || startMinute.getValue() == null) {
            startTimeError.setText("L'heure de début est obligatoire.");
            startTimeError.setVisible(true);
            isValid = false;
        }

        // Validate end time
        if (endHour.getValue() == null || endMinute.getValue() == null) {
            endTimeError.setText("L'heure de fin est obligatoire.");
            endTimeError.setVisible(true);
            isValid = false;
        }

        // Validate date and time relationship
        if (isValid && sessionStartDate != null && sessionEndDate != null &&
                startHour.getValue() != null && startMinute.getValue() != null &&
                endHour.getValue() != null && endMinute.getValue() != null) {

            try {
                LocalTime startTime = LocalTime.of(
                        Integer.parseInt(startHour.getValue()),
                        Integer.parseInt(startMinute.getValue())
                );
                LocalTime endTime = LocalTime.of(
                        Integer.parseInt(endHour.getValue()),
                        Integer.parseInt(endMinute.getValue())
                );

                // Create LocalDateTime objects to compare the full date and time
                LocalDateTime startDateTime = LocalDateTime.of(sessionStartDate, startTime);
                LocalDateTime endDateTime = LocalDateTime.of(sessionEndDate, endTime);

                if (!endDateTime.isAfter(startDateTime)) {
                    endTimeError.setText("L'heure de fin doit être postérieure à l'heure de début.");
                    endTimeError.setVisible(true);
                    isValid = false;
                }
            } catch (NumberFormatException e) {
                startTimeError.setText("Format d'heure invalide.");
                startTimeError.setVisible(true);
                endTimeError.setText("Format d'heure invalide.");
                endTimeError.setVisible(true);
                isValid = false;
            }
        }

        // Validate session type
        if (typeField.getValue() == null) {
            typeError.setText("Le type de session est obligatoire.");
            typeError.setVisible(true);
            isValid = false;
        } else {
            typeError.setVisible(false);
        }

        return isValid;
    }
    private boolean validateAllSessions() {
        boolean allValid = true;
        int sessionCount = 0;

        for (Node node : sessionsContainer.getChildren()) {
            if (node instanceof VBox && node != addSessionButton) {
                sessionCount++;
                if (!validateSession((VBox) node)) {
                    allValid = false;
                }
            }
        }

        if (sessionCount == 0) {
            // You could add a general error message here if needed
            return false;
        }

        return allValid;
    }

    public void saveEvent() {
        if (!validateForm() || !validateAllSessions()) {
            return;
        }

        String titre = titreEvent.getText().trim();
        String description = descriptionEvent.getText().trim();
        LocalDate date_debut = dateDebut.getValue();
        LocalDate date_fin = dateFin.getValue();
        int nb_participant = Integer.parseInt(nbParticipantEvent.getText().trim());

        Event event = new Event();
        event.setTitle(titre);
        event.setDescription(description);
        event.setDate_debut(java.sql.Date.valueOf(date_debut));
        event.setDate_fin(java.sql.Date.valueOf(date_fin));
        event.setMax_participants(nb_participant);
        event.setStatus(Etat.EnAttente.toString());
        event.setImage(uploadedImageName);

        try {
            int id = eventService.ajouterEvent(event);

            for (Node node : sessionsContainer.getChildren()) {
                if (node instanceof VBox && node != addSessionButton) {
                    VBox sessionBox = (VBox) node;

                    // Get components from the session box
                    VBox objectiveBox = (VBox) sessionBox.getChildren().get(0);
                    TextField objectiveField = (TextField) objectiveBox.getChildren().get(1);

                    GridPane timeGrid = (GridPane) sessionBox.getChildren().get(1);

                    // Get date pickers
                    DatePicker startDatePicker = (DatePicker) timeGrid.getChildren().get(2);
                    DatePicker endDatePicker = (DatePicker) timeGrid.getChildren().get(3);

                    // Get time pickers
                    HBox startTimePicker = (HBox) timeGrid.getChildren().get(4);
                    HBox endTimePicker = (HBox) timeGrid.getChildren().get(5);

                    VBox typeBox = (VBox) sessionBox.getChildren().get(2);
                    ComboBox<String> typeField = (ComboBox<String>) typeBox.getChildren().get(1);

                    // Extract time values
                    ComboBox<String> startHourCombo = (ComboBox<String>) startTimePicker.getChildren().get(0);
                    ComboBox<String> startMinuteCombo = (ComboBox<String>) startTimePicker.getChildren().get(2);
                    ComboBox<String> endHourCombo = (ComboBox<String>) endTimePicker.getChildren().get(0);
                    ComboBox<String> endMinuteCombo = (ComboBox<String>) endTimePicker.getChildren().get(2);

                    // Create session object
                    Session session = new Session();
                    session.setEventId(id);
                    session.setObjective(objectiveField.getText());
                    session.setType_session(typeField.getValue());

                    // Set session times with the selected dates
                    LocalTime startTime = LocalTime.of(
                            Integer.parseInt(startHourCombo.getValue()),
                            Integer.parseInt(startMinuteCombo.getValue())
                    );
                    LocalTime endTime = LocalTime.of(
                            Integer.parseInt(endHourCombo.getValue()),
                            Integer.parseInt(endMinuteCombo.getValue())
                    );

                    // Use the selected dates for session start and end
                    LocalDate sessionStartDate = startDatePicker.getValue();
                    LocalDate sessionEndDate = endDatePicker.getValue();

                    session.setStart_hour(sessionStartDate.atTime(startTime));
                    session.setEnd_hour(sessionEndDate.atTime(endTime));

                    // Save session to database
                    sessionService.ajouter(session);
                }
            }

            showAlert("Success", "Événement et sessions ajoutés avec succès!", Alert.AlertType.INFORMATION);

            NavigationUtils.navigateTo("/pi2425/swappy_javafx/events/mesEvents.fxml", "Mes Événements", saveEventButton);
        } catch (SQLException e) {
            showAlert("Error", "Échec de l'ajout de l'événement: " + e.getMessage(), Alert.AlertType.ERROR);
        }
    }

    @FXML
    private void cancelTransaction(){
        Alert confirmation = new Alert(Alert.AlertType.CONFIRMATION);
        confirmation.setTitle("Confirmer la suppression");
        confirmation.setHeaderText("Supprimer l'événement");
        confirmation.setContentText("Êtes-vous sûr d'annuler cette création ? Toutes les données non enregistrées seront perdues.");

        // Customize buttons
        ButtonType yesButton = new ButtonType("Oui", ButtonBar.ButtonData.YES);
        ButtonType noButton = new ButtonType("Non", ButtonBar.ButtonData.NO);
        confirmation.getButtonTypes().setAll(yesButton, noButton);

        // Show dialog and wait for response
        Optional<ButtonType> result = confirmation.showAndWait();

        if (result.isPresent() && result.get() == yesButton) {

            try {

                NavigationUtils.navigateTo("/pi2425/swappy_javafx/events/mesEvents.fxml","mes évenements",cancelAdd);
            } catch (Exception e) {
                e.printStackTrace();
                showAlert("Erreur", "opps! vous ne pouvez pas annulez cette transaction " + e.getMessage(), Alert.AlertType.ERROR);
            }

        }
    }







}
