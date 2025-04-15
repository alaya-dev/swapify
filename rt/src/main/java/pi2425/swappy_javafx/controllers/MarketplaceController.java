package pi2425.swappy_javafx.controllers;

import javafx.concurrent.Task;
import javafx.fxml.FXML;
import javafx.scene.control.*;
import javafx.scene.layout.*;
import javafx.scene.image.*;
import javafx.geometry.*;
import javafx.scene.paint.Color;
import javafx.scene.shape.Rectangle;
import javafx.stage.FileChooser;
import javafx.stage.Stage;
import okhttp3.*;
import org.json.JSONObject;
import pi2425.swappy_javafx.entities.Product;
import pi2425.swappy_javafx.entities.UserSession;
import pi2425.swappy_javafx.services.ProductService;
import java.io.File;
import java.io.IOException;
import java.nio.file.Files;
import java.nio.file.Path;
import java.nio.file.Paths;
import java.nio.file.StandardCopyOption;
import java.sql.SQLException;
import java.util.List;
import java.util.Optional;
import java.util.UUID;
import java.util.concurrent.TimeUnit;
import java.util.stream.Collectors;

import static pi2425.swappy_javafx.utils.NavigationUtils.showAlert;

public class MarketplaceController {

    @FXML private FlowPane productsTilePane;  // Changé de TilePane à FlowPane
    @FXML private TextField searchField;
    private final ProductService productService = new ProductService();
    private List<Product> currentProducts;
    private int currentSoukId;
    private int currentUser = UserSession.getInstance().getUser().getId();

    public void loadProductsForSouk(int soukId) {
        this.currentSoukId = soukId;
        try {
            currentProducts = productService.getProductsBySoukId(soukId);
            displayProducts(currentProducts);
            setupSearchFilter();
        } catch (SQLException e) {
            showAlert("Error", "Failed to load products", Alert.AlertType.ERROR);
            e.printStackTrace();
        }
    }

    private void setupSearchFilter() {
        searchField.textProperty().addListener((observable, oldValue, newValue) -> {
            filterProducts();
        });
    }

    private void filterProducts() {
        String searchText = searchField.getText().toLowerCase();

        List<Product> filteredProducts = currentProducts.stream()
                .filter(product ->
                        product.getProduct_name().toLowerCase().contains(searchText) ||
                                product.getProduct_description().toLowerCase().contains(searchText)
                )
                .collect(Collectors.toList());

        displayProducts(filteredProducts);
    }

    private void displayProducts(List<Product> products) {
        productsTilePane.getChildren().clear();

        for (Product product : products) {
            VBox productCard = createProductCard(
                    product.getProduct_name(),
                    product.getProduct_description(),
                    product.getProduct_price(),
                    product.getOld_price(),
                    product.getOwner_id()
            );

            if (product.getProduct_image() != null && !product.getProduct_image().isEmpty()) {
                Image productImage = loadProductImage(product.getProduct_image());
                if (productImage != null) {
                    addImageToCard(productCard, productImage);
                } else {
                    addPlaceholderImage(productCard);
                }
            } else {
                addPlaceholderImage(productCard);
            }

            productsTilePane.getChildren().add(productCard);
        }
    }

    private VBox createProductCard(String name, String description, double price, double oldPrice, int productOwnerId) {
        VBox card = new VBox();
        card.setStyle("-fx-background-color: white; " +
                      "-fx-effect: dropshadow(gaussian, rgba(0,0,0,0.1), 10, 0, 0, 1); " +
                      "-fx-background-radius: 15; " +
                      "-fx-min-width: 280; -fx-max-width: 280; " +
                      "-fx-min-height: 420;");
        card.setSpacing(10);
        card.setPadding(new Insets(15));

        // Image Container
        StackPane imageContainer = new StackPane();
        imageContainer.setStyle("-fx-background-color: #f8f9fa; " +
                              "-fx-background-radius: 10;");
        imageContainer.setMinHeight(200);
        imageContainer.setMaxHeight(200);
        imageContainer.setPrefHeight(200);
        imageContainer.setMinWidth(250);
        imageContainer.setMaxWidth(250);
        imageContainer.setPrefWidth(250);
        
        // Centre l'imageContainer dans la carte
        imageContainer.setAlignment(Pos.CENTER);
        VBox.setMargin(imageContainer, new Insets(0, 0, 10, 0));

        // Content Container
        VBox content = new VBox(10);
        content.setAlignment(Pos.TOP_LEFT);

        // Product Name
        Label nameLabel = new Label(name);
        nameLabel.setStyle("-fx-font-size: 16; " +
                "-fx-font-weight: bold; " +
                "-fx-text-fill: #2E7D32; " +  // Couleur verte pour correspondre au thème
                "-fx-padding: 0 0 5 0;");
        nameLabel.setWrapText(true);
        nameLabel.setMaxWidth(250);

        // Description
        Label descLabel = new Label(description);
        descLabel.setStyle("-fx-font-size: 13; -fx-text-fill: #666666;");
        descLabel.setWrapText(true);
        descLabel.setMaxWidth(250);
        descLabel.setMaxHeight(40);

        // Price Box
        HBox priceBox = new HBox(10);
        priceBox.setAlignment(Pos.CENTER_LEFT);
        priceBox.setPadding(new Insets(5, 0, 5, 0));

        Label priceLabel = new Label(String.format("%.0f MAD", price));
        priceLabel.setStyle("-fx-font-size: 18; -fx-font-weight: bold; -fx-text-fill: #2E7D32;");

        if (oldPrice > 0) {
            Label oldPriceLabel = new Label(String.format("%.0f MAD", oldPrice));
            oldPriceLabel.setStyle("-fx-font-size: 14; -fx-text-fill: #999; -fx-strikethrough: true;");
            priceBox.getChildren().addAll(priceLabel, oldPriceLabel);
        } else {
            priceBox.getChildren().add(priceLabel);
        }

        // Buttons Container
        HBox buttonContainer = new HBox(10);
        buttonContainer.setAlignment(Pos.CENTER);
        buttonContainer.setPadding(new Insets(10, 0, 0, 0));

        if (currentUser == productOwnerId) {
            Button modifyBtn = new Button("Modifier");
            modifyBtn.setStyle("-fx-background-color: #1976D2; -fx-text-fill: white; " +
                              "-fx-padding: 8 20; -fx-background-radius: 20; " +
                              "-fx-cursor: hand;");
            modifyBtn.setOnAction(event -> modifyProduct(name));

            Button deleteBtn = new Button("Supprimer");
            deleteBtn.setStyle("-fx-background-color: #D32F2F; -fx-text-fill: white; " +
                              "-fx-padding: 8 20; -fx-background-radius: 20; " +
                              "-fx-cursor: hand;");
            deleteBtn.setOnAction(event -> deleteProduct(name));

            buttonContainer.getChildren().addAll(modifyBtn, deleteBtn);
        } else {
            Button contactBtn = new Button("Contacter");
            contactBtn.setStyle("-fx-background-color: #2E7D32; -fx-text-fill: white; " +
                              "-fx-padding: 8 30; -fx-background-radius: 20; " +
                              "-fx-cursor: hand;");
            contactBtn.setOnAction(event -> contactSeller(name));

            buttonContainer.getChildren().add(contactBtn);
        }

        content.getChildren().addAll(nameLabel, descLabel, priceBox, buttonContainer);
        card.getChildren().addAll(imageContainer, content);
        
        return card;
    }

    private void modifyProduct(String productName) {
        Optional<Product> productToModify = currentProducts.stream()
                .filter(p -> p.getProduct_name().equals(productName))
                .findFirst();

        if (!productToModify.isPresent()) {
            showAlert("Error", "Product not found", Alert.AlertType.ERROR);
            return;
        }

        Dialog<Product> dialog = createProductDialog(productToModify.get(), currentSoukId);

        Optional<Product> result = dialog.showAndWait();
        result.ifPresent(updatedProduct -> {
            try {
                productService.modifier(updatedProduct);
                showAlert("Succès", "Produit modifié avec succès!", Alert.AlertType.INFORMATION);
                loadProductsForSouk(currentSoukId);
            } catch (SQLException e) {
                showAlert("Erreur", "Échec de la modification du produit", Alert.AlertType.ERROR);
                e.printStackTrace();
            }
        });
    }

    private void deleteProduct(String productName) {
        Optional<Product> productToDelete = currentProducts.stream()
                .filter(p -> p.getProduct_name().equals(productName))
                .findFirst();
        if (!productToDelete.isPresent()) {
            showAlert("Error", "Product not found", Alert.AlertType.ERROR);
            return;
        }
        Alert confirmation = new Alert(Alert.AlertType.CONFIRMATION);
        confirmation.setTitle("Confirmation de suppression");
        confirmation.setHeaderText(null);
        confirmation.setContentText("Êtes-vous sûr de vouloir supprimer le produit \"" + productName + "\"?");

        Optional<ButtonType> result = confirmation.showAndWait();
        if (result.isPresent() && result.get() == ButtonType.OK) {
            try {
                productService.supprimer(productToDelete.get());
                showAlert("Succès", "Produit supprimé avec succès!", Alert.AlertType.INFORMATION);
                loadProductsForSouk(currentSoukId);
            } catch (SQLException e) {
                showAlert("Erreur", "Échec de la suppression du produit", Alert.AlertType.ERROR);
                e.printStackTrace();
            }
        }
    }

    @FXML
    private void openAddProductDialog() {
        if (currentSoukId == 0) {
            showAlert("Error", "No souk selected", Alert.AlertType.ERROR);
            return;
        }
        openAddProductDialog(currentSoukId);
    }

    private void openAddProductDialog(int soukId) {
        Dialog<Product> dialog = createProductDialog(null, soukId);

        Optional<Product> result = dialog.showAndWait();
        result.ifPresent(product -> {
            try {
                productService.ajouter(product);
                showAlert("Succès", "Produit ajouté avec succès!", Alert.AlertType.INFORMATION);
                loadProductsForSouk(currentSoukId);
            } catch (SQLException e) {
                showAlert("Erreur", "Échec de l'ajout du produit", Alert.AlertType.ERROR);
                e.printStackTrace();
            }
        });
    }

    private Dialog<Product> createProductDialog(Product existingProduct, int soukId) {
        Dialog<Product> dialog = new Dialog<>();
        boolean isEditMode = existingProduct != null;

        dialog.setTitle(isEditMode ? "Modifier le produit" : "Ajouter un nouveau produit");
        dialog.setHeaderText(isEditMode ? "Modifiez les détails du produit" : "Remplissez les détails du produit");

        ButtonType confirmButtonType = new ButtonType(
                isEditMode ? "Enregistrer" : "Ajouter",
                ButtonBar.ButtonData.OK_DONE
        );
        dialog.getDialogPane().getButtonTypes().addAll(confirmButtonType, ButtonType.CANCEL);

        GridPane grid = new GridPane();
        grid.setHgap(10);
        grid.setVgap(10);
        grid.setPadding(new Insets(20, 10, 10, 10));

        TextField nameField = new TextField();
        TextArea descriptionArea = new TextArea();
        descriptionArea.setWrapText(true);
        descriptionArea.setEditable(false);
        TextField priceField = new TextField();
        TextField discountPriceField = new TextField();
        Button uploadImageBtn = new Button("Upload Image");
        ProgressIndicator progressIndicator = new ProgressIndicator();
        progressIndicator.setVisible(false);
        ImageView imagePreview = new ImageView();
        imagePreview.setFitWidth(150);
        imagePreview.setFitHeight(150);
        imagePreview.setPreserveRatio(true);

        if (isEditMode) {
            nameField.setText(existingProduct.getProduct_name());
            descriptionArea.setText(existingProduct.getProduct_description());
            priceField.setText(String.valueOf(existingProduct.getProduct_price()));
            discountPriceField.setText(String.valueOf(existingProduct.getOld_price()));

            if (existingProduct.getProduct_image() != null) {
                Image currentImage = loadProductImage(existingProduct.getProduct_image());
                if (currentImage != null) {
                    imagePreview.setImage(currentImage);
                }
            }
        }

        File[] selectedImageFile = {null};
        String[] uploadedImageName = {isEditMode ? existingProduct.getProduct_image() : null};
        String[] generatedDescription = {isEditMode ? existingProduct.getProduct_description() : null};

        uploadImageBtn.setOnAction(e -> handleImageUpload(
                uploadImageBtn, progressIndicator, descriptionArea,
                imagePreview, selectedImageFile, uploadedImageName, generatedDescription
        ));

        grid.add(new Label("Nom:"), 0, 0);
        grid.add(nameField, 1, 0);
        grid.add(new Label("Description:"), 0, 1);
        grid.add(descriptionArea, 1, 1);
        grid.add(new Label("Prix:"), 0, 2);
        grid.add(priceField, 1, 2);
        grid.add(new Label("Ancien prix:"), 0, 3);
        grid.add(discountPriceField, 1, 3);
        grid.add(uploadImageBtn, 0, 4);
        grid.add(progressIndicator, 1, 4);
        grid.add(imagePreview, 2, 0, 1, 5);

        dialog.getDialogPane().setContent(grid);

        dialog.setResultConverter(dialogButton -> {
            if (dialogButton == confirmButtonType) {
                try {
                    if (nameField.getText().isEmpty() || priceField.getText().isEmpty()) {
                        showAlert("Erreur", "Le nom et le prix sont obligatoires", Alert.AlertType.ERROR);
                        return null;
                    }

                    if (uploadedImageName[0] == null) {
                        showAlert("Erreur", "Veuillez uploader et traiter une image", Alert.AlertType.ERROR);
                        return null;
                    }

                    if (isEditMode) {
                        existingProduct.setProduct_name(nameField.getText());
                        existingProduct.setProduct_description(descriptionArea.getText());
                        existingProduct.setProduct_price(Integer.parseInt(priceField.getText()));
                        existingProduct.setOld_price(discountPriceField.getText().isEmpty() ? 0 : Integer.parseInt(discountPriceField.getText()));
                        existingProduct.setProduct_image(uploadedImageName[0]);
                        return existingProduct;
                    } else {
                        return new Product(
                                UserSession.getInstance().getUser().getId(),
                                soukId,
                                nameField.getText(),
                                descriptionArea.getText(),
                                Integer.parseInt(priceField.getText()),
                                discountPriceField.getText().isEmpty() ? 0 : Integer.parseInt(discountPriceField.getText()),
                                uploadedImageName[0]
                        );
                    }
                } catch (NumberFormatException e) {
                    showAlert("Erreur", "Veuillez entrer des valeurs numériques valides pour les prix", Alert.AlertType.ERROR);
                    return null;
                }
            }
            return null;
        });

        return dialog;
    }

    private void handleImageUpload(
            Button uploadImageBtn,
            ProgressIndicator progressIndicator,
            TextArea descriptionArea,
            ImageView imagePreview,
            File[] selectedImageFile,
            String[] uploadedImageName,
            String[] generatedDescription
    ) {
        Stage stage = (Stage) uploadImageBtn.getScene().getWindow();
        Path imagePath = Paths.get(System.getProperty("user.dir"), "..", "swapify-dev", "public", "uploads", "images");

        FileChooser fileChooser = new FileChooser();
        fileChooser.setTitle("Select Product Image");
        fileChooser.getExtensionFilters().addAll(
                new FileChooser.ExtensionFilter("Image Files", "*.png", "*.jpg", "*.jpeg")
        );
        selectedImageFile[0] = fileChooser.showOpenDialog(stage);

        if (selectedImageFile[0] != null) {
            progressIndicator.setVisible(true);
            descriptionArea.setText("Génération de la description...");
            uploadImageBtn.setDisable(true);

            Task<String> recognitionTask = new Task<>() {
                @Override
                protected String call() throws Exception {
                    String apiUrl = "http://localhost:8000/describe-image";
                    String mimeType = Files.probeContentType(selectedImageFile[0].toPath());
                    MediaType mediaType = MediaType.parse(mimeType != null ? mimeType : "image/jpeg");
                    OkHttpClient okHttpClient = new OkHttpClient.Builder()
                            .connectTimeout(30, TimeUnit.SECONDS)
                            .readTimeout(60, TimeUnit.SECONDS)
                            .writeTimeout(30, TimeUnit.SECONDS)
                            .build();
                    RequestBody requestBody = new MultipartBody.Builder()
                            .setType(MultipartBody.FORM)
                            .addFormDataPart(
                                    "file",
                                    selectedImageFile[0].getName(),
                                    RequestBody.create(selectedImageFile[0], mediaType)
                            )
                            .build();

                    Request request = new Request.Builder()
                            .url(apiUrl)
                            .post(requestBody)
                            .build();

                    try (Response response = okHttpClient.newCall(request).execute()) {
                        if (!response.isSuccessful()) {
                            throw new IOException("Unexpected code: " + response);
                        }

                        String responseBody = response.body().string();
                        JSONObject jsonResponse = new JSONObject(responseBody);
                        return jsonResponse.getString("description");
                    }
                }
            };

            recognitionTask.setOnSucceeded(event -> {
                generatedDescription[0] = recognitionTask.getValue();
                descriptionArea.setText(generatedDescription[0]);
                progressIndicator.setVisible(false);
                uploadImageBtn.setDisable(false);
                imagePreview.setImage(new Image(selectedImageFile[0].toURI().toString()));
                uploadedImageName[0] = saveImageFile(selectedImageFile[0], imagePath.toString());
            });

            recognitionTask.setOnFailed(event -> {
                Throwable ex = recognitionTask.getException();
                descriptionArea.setText("Erreur lors de la génération de la description: " + ex.getMessage());
                progressIndicator.setVisible(false);
                uploadImageBtn.setDisable(false);
            });

            new Thread(recognitionTask).start();
        }
    }

    private void contactSeller(String productName) {
        Alert alert = new Alert(Alert.AlertType.INFORMATION);
        alert.setTitle("Contact Seller");
        alert.setHeaderText(null);
        alert.setContentText("Contact form for: " + productName);
        alert.showAndWait();
    }

    private Image loadProductImage(String imageName) {
        try {
            Path imagePath = Paths.get(System.getProperty("user.dir"), "..", "swapify-dev", "public", "uploads", "images", imageName);
            String imageUri = imagePath.toUri().toString();
            return new Image(imageUri);
        } catch (Exception e) {
            System.err.println("Failed to load product image: " + imageName);
            return null;
        }
    }

    private void addImageToCard(VBox card, Image image) {
        StackPane imageContainer = (StackPane) card.getChildren().get(0);
        ImageView imageView = new ImageView(image);
        imageView.setFitWidth(250);
        imageView.setFitHeight(200);
        imageView.setPreserveRatio(true);
        
        // Ajuste l'image pour remplir l'espace tout en gardant les proportions
        imageView.setSmooth(true);
        imageView.setCache(true);
        
        // Centre l'image dans le StackPane
        StackPane.setAlignment(imageView, Pos.CENTER);
        imageContainer.getChildren().clear();
        imageContainer.getChildren().add(imageView);
    }

    private void addPlaceholderImage(VBox card) {
        StackPane imageContainer = (StackPane) card.getChildren().get(0);
        ImageView imageView = new ImageView();
        imageView.setFitWidth(250);
        imageView.setFitHeight(200);
        imageView.setPreserveRatio(true);

        try {
            Image placeholder = new Image(getClass().getResourceAsStream("/images/placeholder.png"));
            imageView.setImage(placeholder);
        } catch (Exception e) {
            Rectangle placeholderRect = new Rectangle(250, 200);
            placeholderRect.setFill(Color.LIGHTGRAY);
            placeholderRect.setArcWidth(10);
            placeholderRect.setArcHeight(10);
            imageContainer.getChildren().add(placeholderRect);
            return;
        }

        StackPane.setAlignment(imageView, Pos.CENTER);
        imageContainer.getChildren().clear();
        imageContainer.getChildren().add(imageView);
    }

    private String saveImageFile(File imageFile, String storagePath) {
        try {
            File storageDir = new File(storagePath);
            if (!storageDir.exists()) {
                storageDir.mkdirs();
            }

            String fileExtension = imageFile.getName().substring(imageFile.getName().lastIndexOf("."));
            String uniqueFileName = UUID.randomUUID().toString() + fileExtension;

            File savedFile = new File(storageDir, uniqueFileName);
            Files.copy(imageFile.toPath(), savedFile.toPath(), StandardCopyOption.REPLACE_EXISTING);

            return uniqueFileName;
        } catch (IOException e) {
            e.printStackTrace();
            return null;
        }
    }
}