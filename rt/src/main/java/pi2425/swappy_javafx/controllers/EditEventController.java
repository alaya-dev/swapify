package pi2425.swappy_javafx.controllers;

import javafx.application.Platform;
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
import pi2425.swappy_javafx.utils.NavigationUtils;
import pi2425.swappy_javafx.utils.UploadImageUtil;
import pi2425.swappy_javafx.utils.ValidationUtils;

import java.nio.file.Path;
import java.nio.file.Paths;
import java.sql.SQLException;
import java.time.LocalDate;
import java.time.LocalDateTime;
import java.time.LocalTime;
import java.util.HashSet;
import java.util.List;
import java.util.Optional;
import java.util.Set;
import java.util.stream.Collectors;
import java.util.stream.IntStream;

import static pi2425.swappy_javafx.utils.NavigationUtils.showAlert;

public class EditEventController {

    @FXML
    private VBox sessionsContainer;
    @FXML private Button addSessionButton;
    @FXML private Button removeSessionButton;
    @FXML private Button backButton;
    @FXML private TextField titreEvent;
    @FXML private TextArea descriptionEvent;
    @FXML private DatePicker dateDebut;
    @FXML private DatePicker dateFin;
    @FXML private TextField nbParticipantEvent;
    @FXML private Button uploadImageButton;
    @FXML private Label imageLabel;
    @FXML private Button cancelAdd;
    @FXML private Button saveEventButton;
    @FXML private Label titleError;
    @FXML private Label descriptionError;
    @FXML private Label dateDebutError;
    @FXML private Label dateFinError;
    @FXML private Label participantsError;
    @FXML private Label imageError;


    private String uploadedImageName = null;
    private String originalImageName = null;
    protected int eventId;
    private  EventService eventService ;
    private SessionService sessionService ;

    Path imagePath = Paths.get(System.getProperty("user.dir"), "..", "swapify-dev", "public", "uploads", "images");




    @FXML
    private void handleImageUpload() {
        Stage stage = (Stage) imageLabel.getScene().getWindow();
        uploadedImageName = UploadImageUtil.uploadImage(stage, imagePath.toUri().toString());
        if (uploadedImageName != null) {
            imageLabel.setText(uploadedImageName);
        } else {
            imageLabel.setText("aucune fichier sélectionné");
        }
    }

    public void setEventId(int eventId) {
        this.eventId = eventId;
        loadEventData();
    }

    public void setServices(EventService eventService, SessionService sessionService) {
        this.eventService = eventService;
        this.sessionService = sessionService;
    }

    private void loadEventData() {
        try {
            // Initialize services if not set
            if (eventService == null) {
                eventService = new EventService();
            }
            if (sessionService == null) {
                sessionService = new SessionService();
            }

            // Load event and sessions
            Event currentEvent = eventService.getById(eventId);
            if (currentEvent == null) {
                showAlert("Error", "Event not found", Alert.AlertType.ERROR);
                return;
            }

            // Update UI with event data
            Platform.runLater(() -> {
                try {
                    // First update basic event UI
                    updateEventUI(currentEvent);

                    // Then load sessions
                    List<Session> sessions = sessionService.getSessionByEventId(eventId);
                    sessionsContainer.getChildren().clear();

                    if (sessions.isEmpty()) {
                        // Add an empty session if there are none
                        VBox sessionBox = createSessionUI(null);
                        sessionsContainer.getChildren().add(sessionBox);
                    } else {
                        // Add all existing sessions
                        for (Session session : sessions) {
                            VBox sessionBox = createSessionUI(session);
                            sessionsContainer.getChildren().add(sessionBox);
                        }
                    }

                    // Always add the "Add Session" button at the end
                    sessionsContainer.getChildren().add(addSessionButton);
                } catch (SQLException e) {
                    showAlert("Error", "Failed to load sessions: " + e.getMessage(), Alert.AlertType.ERROR);
                }
            });
        } catch (SQLException e) {
            showAlert("Error", "Failed to load event data: " + e.getMessage(), Alert.AlertType.ERROR);
        }
    }

    protected void updateEventUI(Event event) {
        titreEvent.setText(event.getTitle());
        descriptionEvent.setText(event.getDescription());
        dateDebut.setValue(event.getDate_debut().toLocalDate());
        dateFin.setValue(event.getDate_fin().toLocalDate());
        nbParticipantEvent.setText(String.valueOf(event.getMax_participants()));

        if (event.getImage() != null && !event.getImage().isEmpty()) {
            imageLabel.setText(event.getImage());
            originalImageName = event.getImage();
            uploadedImageName = event.getImage();
        }
    }

    private VBox createSessionUI(Session existingSession) {
        // Main structure remains the same
        VBox sessionBox = new VBox(10);
        sessionBox.setStyle("-fx-border-color: #E0E0E0; -fx-border-style: solid; -fx-border-width: 1; -fx-border-radius: 2; -fx-padding: 16; -fx-spacing: 10;");

        // Objective box remains the same
        VBox objectiveBox = new VBox(4);
        Label objectiveLabel = new Label("Objectif de la session *");
        objectiveLabel.setStyle("-fx-text-fill: #323130; -fx-font-weight: bold;");
        TextField sessionName = new TextField();
        sessionName.setPromptText("Titre de la session");
        sessionName.setPrefHeight(32);
        sessionName.setMaxWidth(Double.MAX_VALUE);
        sessionName.setStyle("-fx-background-radius: 2; -fx-border-radius: 2; -fx-border-color: #8A8886; -fx-padding: 8; -fx-font-size: 14;");

        if (existingSession != null) {
            sessionName.setText(existingSession.getObjective());
        }

        Label objectiveError = new Label();
        objectiveError.setStyle("-fx-text-fill: #a4262c; -fx-font-size: 12;");
        objectiveError.setVisible(false);
        objectiveBox.getChildren().addAll(objectiveLabel, sessionName, objectiveError);

        // Time controls setup
        GridPane timeGrid = new GridPane();
        timeGrid.setHgap(16);
        timeGrid.setVgap(4);

        // Column constraints for 50/50 split
        ColumnConstraints col1 = new ColumnConstraints();
        col1.setPercentWidth(50);
        ColumnConstraints col2 = new ColumnConstraints();
        col2.setPercentWidth(50);
        timeGrid.getColumnConstraints().addAll(col1, col2);

        // Row constraints
        RowConstraints labelRow = new RowConstraints();
        RowConstraints pickerRow = new RowConstraints();
        RowConstraints errorRow = new RowConstraints();
        errorRow.setPrefHeight(20);
        timeGrid.getRowConstraints().addAll(labelRow, pickerRow, errorRow);

        // Start time components
        Label startLabel = new Label("Heure début *");
        startLabel.setStyle("-fx-text-fill: #323130; -fx-font-weight: bold;");
        DatePicker startDatePicker = new DatePicker();
        startDatePicker.setPromptText("Date de début");
        startDatePicker.setPrefHeight(32);
        startDatePicker.setMaxWidth(Double.MAX_VALUE);
        startDatePicker.setStyle("-fx-background-radius: 2; -fx-border-radius: 2; -fx-border-color: #8A8886; -fx-font-size: 14;");

        // Initialize with the event start date if no session exists yet, otherwise use session date
        if (existingSession != null) {
            startDatePicker.setValue(existingSession.getStart_hour().toLocalDate());
        } else {
            startDatePicker.setValue(dateDebut.getValue());
        }

        HBox startTimePicker = new HBox(5);
        ComboBox<String> startHourCombo = new ComboBox<>();
        startHourCombo.getItems().addAll(IntStream.rangeClosed(0, 23).mapToObj(i -> String.format("%02d", i)).collect(Collectors.toList()));
        startHourCombo.setPromptText("HH");
        startHourCombo.setPrefWidth(80);
        startHourCombo.setStyle("-fx-background-radius: 2; -fx-border-radius: 2; -fx-border-color: #8A8886; -fx-font-size: 14;");

        ComboBox<String> startMinuteCombo = new ComboBox<>();
        startMinuteCombo.getItems().addAll(IntStream.rangeClosed(0, 59).mapToObj(i -> String.format("%02d", i)).collect(Collectors.toList()));
        startMinuteCombo.setPromptText("MM");
        startMinuteCombo.setPrefWidth(80);
        startMinuteCombo.setStyle("-fx-background-radius: 2; -fx-border-radius: 2; -fx-border-color: #8A8886; -fx-font-size: 14;");

        Label startSeparator = new Label(":");
        startSeparator.setStyle("-fx-font-size: 14; -fx-alignment: center;");
        startTimePicker.getChildren().addAll(startHourCombo, startSeparator, startMinuteCombo);

        Label startTimeError = new Label();
        startTimeError.setStyle("-fx-text-fill: #a4262c; -fx-font-size: 12;");
        startTimeError.setVisible(false);

        // End time components
        Label endLabel = new Label("Heure fin *");
        endLabel.setStyle("-fx-text-fill: #323130; -fx-font-weight: bold;");
        DatePicker endDatePicker = new DatePicker();
        endDatePicker.setPromptText("Date de fin");
        endDatePicker.setPrefHeight(32);
        endDatePicker.setMaxWidth(Double.MAX_VALUE);
        endDatePicker.setStyle("-fx-background-radius: 2; -fx-border-radius: 2; -fx-border-color: #8A8886; -fx-font-size: 14;");

        // Initialize with the event start date if no session exists yet, otherwise use session date
        if (existingSession != null) {
            endDatePicker.setValue(existingSession.getEnd_hour().toLocalDate());
        } else {
            endDatePicker.setValue(dateFin.getValue());
        }

        HBox endTimePicker = new HBox(5);
        ComboBox<String> endHourCombo = new ComboBox<>();
        endHourCombo.getItems().addAll(IntStream.rangeClosed(0, 23).mapToObj(i -> String.format("%02d", i)).collect(Collectors.toList()));
        endHourCombo.setPromptText("HH");
        endHourCombo.setPrefWidth(80);
        endHourCombo.setStyle("-fx-background-radius: 2; -fx-border-radius: 2; -fx-border-color: #8A8886; -fx-font-size: 14;");

        ComboBox<String> endMinuteCombo = new ComboBox<>();
        endMinuteCombo.getItems().addAll(IntStream.rangeClosed(0, 59).mapToObj(i -> String.format("%02d", i)).collect(Collectors.toList()));
        endMinuteCombo.setPromptText("MM");
        endMinuteCombo.setPrefWidth(80);
        endMinuteCombo.setStyle("-fx-background-radius: 2; -fx-border-radius: 2; -fx-border-color: #8A8886; -fx-font-size: 14;");

        Label endSeparator = new Label(":");
        endSeparator.setStyle("-fx-font-size: 14; -fx-alignment: center;");
        endTimePicker.getChildren().addAll(endHourCombo, endSeparator, endMinuteCombo);

        Label endTimeError = new Label();
        endTimeError.setStyle("-fx-text-fill: #a4262c; -fx-font-size: 12;");
        endTimeError.setVisible(false);

        // Populate time values if we have an existing session
        if (existingSession != null) {
            LocalDateTime startTime = existingSession.getStart_hour();
            LocalDateTime endTime = existingSession.getEnd_hour();
            startHourCombo.setValue(String.format("%02d", startTime.getHour()));
            startMinuteCombo.setValue(String.format("%02d", startTime.getMinute()));
            endHourCombo.setValue(String.format("%02d", endTime.getHour()));
            endMinuteCombo.setValue(String.format("%02d", endTime.getMinute()));
        }

        // Add components to grid
        timeGrid.add(startLabel, 0, 0);
        timeGrid.add(endLabel, 1, 0);
        timeGrid.add(startDatePicker, 0, 1);
        timeGrid.add(endDatePicker, 1, 1);
        timeGrid.add(startTimePicker, 0, 2);
        timeGrid.add(endTimePicker, 1, 2);
        timeGrid.add(startTimeError, 0, 3);
        timeGrid.add(endTimeError, 1, 3);

        // Session Type box remains the same
        VBox typeBox = new VBox(4);
        Label typeLabel = new Label("Type de session *");
        typeLabel.setStyle("-fx-text-fill: #323130; -fx-font-weight: bold;");
        ComboBox<String> sessionType = new ComboBox<>();
        sessionType.getItems().addAll("En ligne", "Présentiel");
        sessionType.setPromptText("Veuillez sélectionner un type pour cette session");
        sessionType.setPrefHeight(32);
        sessionType.setMaxWidth(Double.MAX_VALUE);
        sessionType.setStyle("-fx-background-radius: 2; -fx-border-radius: 2; -fx-border-color: #8A8886; -fx-font-size: 14;");

        // Set type if we have an existing session
        if (existingSession != null) {
            sessionType.setValue(existingSession.getType_session());
        }

        Label typeError = new Label();
        typeError.setStyle("-fx-text-fill: #a4262c; -fx-font-size: 12;");
        typeError.setVisible(false);
        typeBox.getChildren().addAll(typeLabel, sessionType, typeError);

        // Remove Button remains the same
        Button removeButton = new Button("Supprimer");
        removeButton.setPrefHeight(32);
        removeButton.setMaxWidth(Double.MAX_VALUE);
        removeButton.setStyle("-fx-background-color: #c13c37; -fx-text-fill: white; -fx-background-radius: 2; -fx-font-size: 14; -fx-alignment: center;");
        removeButton.setOnAction(event -> {
            boolean isExistingSession = existingSession != null;
            Alert confirmation = new Alert(Alert.AlertType.CONFIRMATION);
            confirmation.setTitle("Confirmer la suppression");
            confirmation.setHeaderText("Supprimer la session");
            confirmation.setContentText(isExistingSession?
                    "Voulez-vous vraiment supprimer cette session? Cette action est irréversible.":
                    "Supprimer cette session non enregistrée?");
            Optional<ButtonType> result = confirmation.showAndWait();
            if (result.isPresent() && result.get() == ButtonType.OK) {
                sessionsContainer.getChildren().remove(sessionBox);
            }
        });

        // Add all components to the session box
        sessionBox.getChildren().addAll(objectiveBox, timeGrid, typeBox, removeButton);

        // Store session ID if it exists
        if (existingSession != null) {
            sessionBox.setUserData(existingSession.getId());
        }

        return sessionBox;
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

    @FXML
    private void addSessionToEvent() {
        // Create a new session UI with no existing data
        VBox sessionBox = createSessionUI(null);

        // Add to container (before the Add Session button)
        int lastIndex = sessionsContainer.getChildren().size();
        if (lastIndex > 0 && sessionsContainer.getChildren().get(lastIndex - 1) == addSessionButton) {
            sessionsContainer.getChildren().add(lastIndex - 1, sessionBox);
        } else {
            sessionsContainer.getChildren().add(sessionBox);
        }
    }

    protected void loadEventSessions(Event event) {
        try {
            List<Session> sessions = sessionService.getSessionByEventId(event.getId());
            sessionsContainer.getChildren().clear();

            if (sessions.isEmpty()) {
                // Create a new empty session if there are none
                addSessionToEvent();
            } else {
                // Add each existing session
                for (Session session : sessions) {
                    VBox sessionBox = createSessionUI(session);
                    sessionsContainer.getChildren().add(sessionBox);
                }
            }

            // Always add the "Add Session" button at the end
            sessionsContainer.getChildren().add(addSessionButton);
        } catch (SQLException e) {
            showAlert("Error", "Failed to load sessions: " + e.getMessage(), Alert.AlertType.ERROR);
        }
    }
    private boolean validateSession(VBox sessionBox) {
        boolean isValid = true;

        // Get all components from the session box
        VBox objectiveBox = (VBox) sessionBox.getChildren().get(0);
        TextField objectiveField = (TextField) objectiveBox.getChildren().get(1);
        Label objectiveError = (Label) objectiveBox.getChildren().get(2);

        GridPane timeGrid = (GridPane) sessionBox.getChildren().get(1);

        // Get date pickers and errors
        DatePicker startDatePicker = (DatePicker) timeGrid.getChildren().get(0);
        DatePicker endDatePicker = (DatePicker) timeGrid.getChildren().get(1);
        Label startTimeError = (Label) timeGrid.getChildren().get(6);
        Label endTimeError = (Label) timeGrid.getChildren().get(7);

        VBox typeBox = (VBox) sessionBox.getChildren().get(2);
        ComboBox<String> typeField = (ComboBox<String>) typeBox.getChildren().get(1);
        Label typeError = (Label) typeBox.getChildren().get(2);

        // Get time pickers
        HBox startTimePicker = (HBox) timeGrid.getChildren().get(2);
        ComboBox<String> startHour = (ComboBox<String>) startTimePicker.getChildren().get(0);
        ComboBox<String> startMinute = (ComboBox<String>) startTimePicker.getChildren().get(2);

        HBox endTimePicker = (HBox) timeGrid.getChildren().get(3);
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
        if (startDatePicker.getValue() == null) {
            startTimeError.setText("La date de début est obligatoire.");
            startTimeError.setVisible(true);
            isValid = false;
        } else {
            startTimeError.setVisible(false);
        }

        if (endDatePicker.getValue() == null) {
            endTimeError.setText("La date de fin est obligatoire.");
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
        } else {
            startTimeError.setVisible(false);
        }

        // Validate end time
        if (endHour.getValue() == null || endMinute.getValue() == null) {
            endTimeError.setText("L'heure de fin est obligatoire.");
            endTimeError.setVisible(true);
            isValid = false;
        } else {
            endTimeError.setVisible(false);
        }

        // Validate time relationship
        if (isValid && startHour.getValue() != null && startMinute.getValue() != null
                && endHour.getValue() != null && endMinute.getValue() != null
                && startDatePicker.getValue() != null && endDatePicker.getValue() != null) {
            try {
                LocalTime startTime = LocalTime.of(
                        Integer.parseInt(startHour.getValue()),
                        Integer.parseInt(startMinute.getValue())
                );
                LocalTime endTime = LocalTime.of(
                        Integer.parseInt(endHour.getValue()),
                        Integer.parseInt(endMinute.getValue())
                );

                // Get the dates
                LocalDate startDate = startDatePicker.getValue();
                LocalDate endDate = endDatePicker.getValue();

                // Check date and time combinations
                LocalDateTime startDateTime = LocalDateTime.of(startDate, startTime);
                LocalDateTime endDateTime = LocalDateTime.of(endDate, endTime);

                // Check if end is after start
                if (!endDateTime.isAfter(startDateTime)) {
                    endTimeError.setText("La date/heure de fin doit être postérieure à la date/heure de début.");
                    endTimeError.setVisible(true);
                    isValid = false;
                } else {
                    endTimeError.setVisible(false);
                }

                // Check if session dates are within event dates
                LocalDate eventStartDate = dateDebut.getValue();
                LocalDate eventEndDate = dateFin.getValue();

                if (startDate.isBefore(eventStartDate) || startDate.isAfter(eventEndDate)) {
                    startTimeError.setText("La date de début doit être comprise entre les dates de l'événement.");
                    startTimeError.setVisible(true);
                    isValid = false;
                }

                if (endDate.isBefore(eventStartDate) || endDate.isAfter(eventEndDate)) {
                    endTimeError.setText("La date de fin doit être comprise entre les dates de l'événement.");
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

    public void modifierEvent() {
        if (!validateForm() || !validateAllSessions()) {
            return;
        }

        String titre = titreEvent.getText().trim();
        String description = descriptionEvent.getText().trim();
        LocalDate date_debut = dateDebut.getValue();
        LocalDate date_fin = dateFin.getValue();
        int nb_participant = Integer.parseInt(nbParticipantEvent.getText().trim());

        Event event = new Event();
        event.setId(eventId);
        event.setTitle(titre);
        event.setDescription(description);
        event.setDate_debut(java.sql.Date.valueOf(date_debut));
        event.setDate_fin(java.sql.Date.valueOf(date_fin));
        event.setMax_participants(nb_participant);
        event.setStatus(Etat.EnAttente.toString());
        event.setImage(uploadedImageName != null ? uploadedImageName : originalImageName);

        try {
            eventService.modifier(event);
            List<Session> existingSessions = sessionService.getSessionByEventId(eventId);
            Set<Integer> processedSessionIds = new HashSet<>();
            int sessionIndex = 0;

            for (Node node : sessionsContainer.getChildren()) {
                if (node instanceof VBox && node != addSessionButton) {
                    VBox sessionBox = (VBox) node;
                    VBox objectiveBox = (VBox) sessionBox.getChildren().get(0);
                    TextField objectiveField = (TextField) objectiveBox.getChildren().get(1);

                    GridPane timeGrid = (GridPane) sessionBox.getChildren().get(1);
                    DatePicker startDatePicker = (DatePicker) timeGrid.getChildren().get(0);
                    DatePicker endDatePicker = (DatePicker) timeGrid.getChildren().get(1);

                    HBox startTimePicker = (HBox) timeGrid.getChildren().get(2);
                    HBox endTimePicker = (HBox) timeGrid.getChildren().get(3);

                    VBox typeBox = (VBox) sessionBox.getChildren().get(2);
                    ComboBox<String> typeField = (ComboBox<String>) typeBox.getChildren().get(1);

                    ComboBox<String> startHourCombo = (ComboBox<String>) startTimePicker.getChildren().get(0);
                    ComboBox<String> startMinuteCombo = (ComboBox<String>) startTimePicker.getChildren().get(2);
                    ComboBox<String> endHourCombo = (ComboBox<String>) endTimePicker.getChildren().get(0);
                    ComboBox<String> endMinuteCombo = (ComboBox<String>) endTimePicker.getChildren().get(2);

                    LocalTime startTime = LocalTime.of(
                            Integer.parseInt(startHourCombo.getValue()),
                            Integer.parseInt(startMinuteCombo.getValue())
                    );
                    LocalTime endTime = LocalTime.of(
                            Integer.parseInt(endHourCombo.getValue()),
                            Integer.parseInt(endMinuteCombo.getValue())
                    );

                    // Get selected dates
                    LocalDate startDate = startDatePicker.getValue();
                    LocalDate endDate = endDatePicker.getValue();

                    // Create LocalDateTime objects combining date and time
                    LocalDateTime startDateTime = LocalDateTime.of(startDate, startTime);
                    LocalDateTime endDateTime = LocalDateTime.of(endDate, endTime);

                    if (sessionIndex < existingSessions.size()) {
                        // Update existing session
                        Session existingSession = existingSessions.get(sessionIndex);
                        existingSession.setObjective(objectiveField.getText());
                        existingSession.setType_session(typeField.getValue());
                        existingSession.setStart_hour(startDateTime);
                        existingSession.setEnd_hour(endDateTime);
                        sessionService.modifier(existingSession);
                        processedSessionIds.add(existingSession.getId());
                    } else {
                        // Create new session
                        Session newSession = new Session();
                        newSession.setEventId(eventId);
                        newSession.setObjective(objectiveField.getText());
                        newSession.setType_session(typeField.getValue());
                        newSession.setStart_hour(startDateTime);
                        newSession.setEnd_hour(endDateTime);
                        sessionService.ajouter(newSession);
                    }
                    sessionIndex++;
                }
            }

            // Delete sessions that no longer exist in the UI
            for (Session existingSession : existingSessions) {
                if (!processedSessionIds.contains(existingSession.getId())) {
                    sessionService.supprimer(existingSession);
                }
            }

            showAlert("Success", "Événement et sessions modifié avec succès!", Alert.AlertType.INFORMATION);
            NavigationUtils.navigateTo("/pi2425/swappy_javafx/events/mesEvents.fxml", "Mes Événements", saveEventButton);
        } catch (SQLException e) {
            showAlert("Error", "Échec de modification de l'événement: " + e.getMessage(), Alert.AlertType.ERROR);
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
