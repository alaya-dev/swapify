package pi2425.swappy_javafx.controllers;

import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.scene.Parent;
import javafx.scene.Scene;
import javafx.scene.control.*;
import javafx.scene.layout.*;
import javafx.geometry.Insets;
import javafx.stage.Stage;
import pi2425.swappy_javafx.entities.Souk;
import pi2425.swappy_javafx.entities.UserSession;
import pi2425.swappy_javafx.services.SoukService;

import java.io.IOException;
import java.sql.SQLException;
import java.time.Instant;
import java.time.ZoneId;
import java.time.format.DateTimeFormatter;
import java.util.List;
import java.util.Optional;

public class SoukController {

    @FXML
    private TilePane souksTilePane;
    private SoukService soukService = new SoukService();
    private int currentUserId = UserSession.getInstance().getUser().getId();

    @FXML
    public void initialize() {
        try {
            loadAllSouks();
        } catch (SQLException e) {
            showAlert("Erreur", "Erreur de chargement des souks", Alert.AlertType.ERROR);
            e.printStackTrace();
        }
    }

    private void loadAllSouks() throws SQLException {
        souksTilePane.getChildren().clear();
        List<Souk> allSouks = soukService.afficher();

        for (Souk souk : allSouks) {
            boolean isParticipant = soukService.isUserParticipant(currentUserId, souk.getSouk_id());
            VBox soukCard = createSoukCard(souk, isParticipant);
            souksTilePane.getChildren().add(soukCard);
        }
    }

    private VBox createSoukCard(Souk souk, boolean isParticipant) {
        VBox card = new VBox();
        card.setStyle("-fx-background-color: white; -fx-background-radius: 10; -fx-border-radius: 10; " +
                "-fx-effect: dropshadow(three-pass-box, rgba(0,0,0,0.1), 5, 0, 0, 0);");
        card.setSpacing(10);
        card.setPrefSize(300, 200);

        // Header
        HBox header = new HBox();
        String headerColor = isParticipant ? "#2196F3" : "#9C27B0";
        header.setStyle("-fx-background-color: " + headerColor + "; -fx-background-radius: 10 10 0 0; -fx-padding: 15;");
        header.setAlignment(javafx.geometry.Pos.CENTER_LEFT);

        Label nameLabel = new Label(souk.getSouk_name());
        nameLabel.setStyle("-fx-text-fill: white; -fx-font-size: 18px; -fx-font-weight: bold;");
        header.getChildren().add(nameLabel);

        // Content
        VBox content = new VBox();
        content.setStyle("-fx-padding: 0 15 15 15;");
        content.setSpacing(10);

        // Dates
        HBox startDateBox = new HBox(10);
        Label startDateLabel = new Label("Date début:");
        startDateLabel.setStyle("-fx-text-fill: #666;");
        Label startDateValue = new Label(formatDate(souk.getSouk_start()));
        startDateValue.setStyle("-fx-font-weight: bold;");
        startDateBox.getChildren().addAll(startDateLabel, startDateValue);

        HBox endDateBox = new HBox(10);
        Label endDateLabel = new Label("Date fin:");
        endDateLabel.setStyle("-fx-text-fill: #666;");
        Label endDateValue = new Label(formatDate(souk.getSouk_end()));
        endDateValue.setStyle("-fx-font-weight: bold;");
        endDateBox.getChildren().addAll(endDateLabel, endDateValue);

        // Status
        HBox statusBox = new HBox(10);
        Label statusLabel = new Label("Statut:");
        statusLabel.setStyle("-fx-text-fill: #666;");
        Label statusValue = new Label();
        statusValue.setStyle("-fx-font-weight: bold;");

        boolean isActive = Instant.now().isBefore(souk.getSouk_end());
        if (isActive) {
            statusValue.setText("Actif");
            statusValue.setStyle("-fx-text-fill: #4CAF50;");
        } else {
            statusValue.setText("Terminé");
            statusValue.setStyle("-fx-text-fill: #F44336;");
        }
        statusBox.getChildren().addAll(statusLabel, statusValue);

        // Buttons
        HBox buttonsBox = new HBox(10);
        buttonsBox.setAlignment(javafx.geometry.Pos.CENTER_RIGHT);
        buttonsBox.setStyle("-fx-padding: 10 0 0 0;");

        Button viewProductsButton = new Button("Voir les produits");
        viewProductsButton.setStyle("-fx-background-color: #2196F3; -fx-text-fill: white;");
        viewProductsButton.setOnAction(e -> openProductsView(souk.getSouk_id()));

        if (isParticipant) {
            Button addProductButton = new Button("Ajouter produit");
            addProductButton.setStyle("-fx-background-color: #FF9800; -fx-text-fill: white;");
            addProductButton.setOnAction(e -> openAddProductView(souk.getSouk_id()));

            if (!isActive) {
                addProductButton.setDisable(true);
            }
            buttonsBox.getChildren().addAll(viewProductsButton, addProductButton);
        } else {
            Button joinButton = new Button("Rejoindre");
            joinButton.setStyle("-fx-background-color: #4CAF50; -fx-text-fill: white;");
            joinButton.setOnAction(e -> joinSouk(souk.getSouk_id()));
            buttonsBox.getChildren().addAll(viewProductsButton, joinButton);
        }

        // Assemble card
        content.getChildren().addAll(startDateBox, endDateBox, statusBox, buttonsBox);
        card.getChildren().addAll(header, content);

        return card;
    }

    private void joinSouk(int soukId) {
        try {
            soukService.joinSouk(currentUserId, soukId);
            showAlert("Succès", "Vous avez rejoint le souk avec succès!", Alert.AlertType.INFORMATION);
            loadAllSouks();
        } catch (SQLException e) {
            showAlert("Erreur", "Erreur lors de la tentative de rejoindre le souk", Alert.AlertType.ERROR);
            e.printStackTrace();
        }
    }

    private String formatDate(Instant instant) {
        return DateTimeFormatter.ofPattern("dd/MM/yyyy")
                .withZone(ZoneId.systemDefault())
                .format(instant);
    }


    private void openProductsView(int soukId) {
        try {
            // Load the marketplace FXML
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/pi2425/swappy_javafx/souk/Souk.fxml"));
            Parent root = loader.load();

            // Get the controller and pass the soukId
            MarketplaceController marketplaceController = loader.getController();
            marketplaceController.loadProductsForSouk(soukId);

            // Get the current stage and set the new scene
            Stage stage = (Stage) souksTilePane.getScene().getWindow();
            stage.setScene(new Scene(root));
            stage.show();

        } catch (IOException e) {
            showAlert("Erreur", "Impossible de charger la marketplace", Alert.AlertType.ERROR);
            e.printStackTrace();
        }
    }

    private void openAddProductView(int soukId) {
        // À implémenter: navigation vers l'ajout de produit
        showAlert("Information", "Ajout de produit au souk ID: " + soukId, Alert.AlertType.INFORMATION);
    }

    private void showAlert(String title, String message, Alert.AlertType type) {
        Alert alert = new Alert(type);
        alert.setTitle(title);
        alert.setHeaderText(null);
        alert.setContentText(message);
        alert.showAndWait();
    }

}