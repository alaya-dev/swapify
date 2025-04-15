package pi2425.swappy_javafx.controllers;

import javafx.fxml.FXML;
import javafx.scene.control.TextField;
import javafx.scene.image.Image;
import javafx.scene.image.ImageView;
import javafx.stage.Stage;
import pi2425.swappy_javafx.entities.User;
import pi2425.swappy_javafx.entities.UserSession;
import pi2425.swappy_javafx.services.AuthServiceImpl;
import pi2425.swappy_javafx.utils.LoadExternalImage;
import pi2425.swappy_javafx.utils.NavigationUtils;

import java.io.IOException;
import java.sql.SQLException;

public class LoginController {

    @FXML
    private ImageView logo;
    @FXML
    private ImageView thumbnail;

    @FXML
    private TextField emailField;
    @FXML
    private TextField passwordField;

    AuthServiceImpl authService = new AuthServiceImpl();

    public void initialize() {
        System.out.println("LoginController initialized!");


        Image image1 = LoadExternalImage.loadExternalImage("assets/images/logoW.png");
        Image image = LoadExternalImage.loadExternalImage("assets/images/logo1.png"); // "logo.png" is the image name
        logo.setImage(image1);
        thumbnail.setImage(image);


    }
    public void handleBackArrowClick(){
        try {

            NavigationUtils.navigateTo("/pi2425/swappy_javafx/events/index.fxml", "Welcome",emailField);
        } catch (Exception e) {
            e.printStackTrace();
            showAlert("Navigation Error", "Could not go back");
        }
    }
    public void handleSignupClick(){
        try {
            Stage stage = (Stage) emailField.getScene().getWindow();
            NavigationUtils.navigateTo("/views/signup.fxml", "Sign Up",emailField);
        } catch (Exception e) {
            e.printStackTrace();
            showAlert("Navigation Error", "Could not load signup page");
        }
    }
    public void handleLogin() throws SQLException {
        String email = emailField.getText().trim();
        String password = passwordField.getText().trim();
        User user=authService.authenticate(email, password);
        if (user !=null) {
            try {

                UserSession.createSession(user);

                // Make sure this path is correct relative to your resources folder
                NavigationUtils.navigateTo("/pi2425/swappy_javafx/events/mesEvents.fxml", "Swapify - Main",emailField);
            } catch (Exception e) {
                e.printStackTrace();
                showAlert("Navigation Error", "Could not load the main application");
            }
        } else {
            showAlert("Login Failed", "Invalid email or password");
        }
    }

    private void showAlert(String title, String message) {
        // Implement a proper alert dialog
        System.out.println(title + ": " + message);
        // In real app, use Alert or custom dialog
    }

}
