package pi2425.swappy_javafx.controllers;

import javafx.application.Platform;
import javafx.event.ActionEvent;
import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.fxml.Initializable;
import javafx.scene.Node;
import javafx.scene.Parent;
import javafx.scene.Scene;
import javafx.scene.control.*;
import javafx.scene.image.Image;
import javafx.scene.image.ImageView;
import javafx.scene.layout.HBox;
import javafx.stage.Stage;
import pi2425.swappy_javafx.entities.UserSession;
import pi2425.swappy_javafx.services.EventService;
import pi2425.swappy_javafx.services.SessionService;
import pi2425.swappy_javafx.utils.LoadExternalImage;
import pi2425.swappy_javafx.utils.NavigationUtils;

import java.io.IOException;
import java.net.URL;
import java.util.ResourceBundle;

public class HeaderController implements Initializable {

    @FXML
    private HBox navLinks;
    @FXML private HBox userActions;
    @FXML private ImageView logo;


    @Override
    public void initialize(URL location, ResourceBundle resources) {
        setupLogo();
        updateAuthState();
        setupNavLinks();
    }

    private void setupLogo() {
        try {
            Image image = LoadExternalImage.loadExternalImage("assets/images/logo1.png"); // "logo.png" is the image name
            logo.setImage(image);
        } catch (Exception e) {
            System.err.println("Could not load logo image");
        }
    }

    public void updateAuthState() {
        userActions.getChildren().clear();

        if (UserSession.getInstance() != null && UserSession.getInstance().getUser() != null) {
            // User is logged in - create dropdown menu
            createUserDropdown();
        } else {
            // User is not logged in - show login/register buttons
            createGuestButtons();
        }
    }


    private void createGuestButtons() {
        Button loginBtn = new Button("Connexion");
        loginBtn.getStyleClass().add("login-button");
        loginBtn.setOnAction(e -> NavigationUtils.navigateTo("/pi2425/swappy_javafx/Authentification/login.fxml","login",logo));

        Button registerBtn = new Button("Inscription");
        registerBtn.getStyleClass().add("register-button");
        registerBtn.setOnAction(e -> showRegistrationDialog());

        userActions.getChildren().addAll(loginBtn, registerBtn);
    }

    private void createUserDropdown() {
        try {
            // Create profile button with image
//            ImageView profileImage = new ImageView();
//            // Load user image - you'll need to implement this
//            Image image = LoadExternalImage.loadExternalImage(UserSession.getInstance().getUser());
//            profileImage.setImage(image);
//            profileImage.setFitHeight(32);
//            profileImage.setFitWidth(32);
//            profileImage.getStyleClass().add("profile-image");

            MenuButton userMenu = new MenuButton();
            //userMenu.setGraphic(profileImage);
            userMenu.getStyleClass().add("user-menu-button");

            // Create menu items
            MenuItem profileItem = new MenuItem("Profile");
            profileItem.setOnAction(e -> showUserProfile());

            // chat-app
            MenuItem chatItem = new MenuItem("Conversation");
            chatItem.setOnAction(e ->  NavigationUtils.navigateTo("/pi2425/swappy_javafx/conversation/conversation.fxml","mes Évenements",logo));

            //live Event souk Ite
            MenuItem souk = new MenuItem("Live product");
            souk.setOnAction(e -> NavigationUtils.navigateTo("/pi2425/swappy_javafx/souk/view.fxml","mes Évenements",logo));

            MenuItem eventsItem = new MenuItem("Mes Evenements");
            eventsItem.setOnAction(e -> NavigationUtils.navigateTo("/pi2425/swappy_javafx/events/mesEvents.fxml","mes Évenements",logo));

            MenuItem logoutItem = new MenuItem("Déconnexion");
            logoutItem.setOnAction(e -> handleLogout());

            // Add items to menu
            userMenu.getItems().addAll(profileItem, chatItem,eventsItem,souk, logoutItem);

            userActions.getChildren().add(userMenu);

        } catch (Exception e) {
            e.printStackTrace();
            // Fallback to simple button if menu fails
            Button profileBtn = new Button(UserSession.getInstance().getUser().getPrenom());
            profileBtn.getStyleClass().add("profile-button");
            //profileBtn.setOnAction(e -> showUserProfile());
            userActions.getChildren().add(profileBtn);
        }
    }

    private void showUserProfile() {
        // Navigation logic to profile page
    }

    private void handleLogout() {
        UserSession.clearSession();
        updateAuthState();
    }

    private void showLoginDialog() {
        // Show login dialog
    }

    private void showRegistrationDialog() {
        // Show registration dialog
    }

    private void conversation() {

    }

    private void setupNavLinks() {
        for (Node node : navLinks.getChildren()) {
            if (node instanceof Label) {
                Label label = (Label) node;
                label.setOnMouseClicked(e -> handleNavLinkClick(label.getText()));
            }
        }
    }

    private void handleNavLinkClick(String pageName) {
        try {
            String fxmlPath = "";
            String title = "";

            switch (pageName) {
                case "Acceuil":
                    fxmlPath = "/pi2425/swappy_javafx/events/index.fxml";
                    title = "Accueil";
                    break;
                case "Annonces":
                    fxmlPath = "/pi2425/swappy_javafx/annonces/list.fxml";
                    title = "Annonces";
                    break;
                case "Evenements":
                    fxmlPath = "/pi2425/swappy_javafx/events/index.fxml";
                    title = "Événements";
                    break;
                case "Blogs":
                    fxmlPath = "/pi2425/swappy_javafx/blogs/list.fxml";
                    title = "Blogs";
                    break;
                case "Contact":
                    fxmlPath = "/pi2425/swappy_javafx/contact/contact.fxml";
                    title = "Contact";
                    break;
            }

            if (!fxmlPath.isEmpty()) {
                NavigationUtils.navigateTo(fxmlPath, title,logo);
            }
        } catch (Exception e) {
            e.printStackTrace();
        }
    }

}


