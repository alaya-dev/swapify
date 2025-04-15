package pi2425.swappy_javafx.utils;

import javafx.event.ActionEvent;
import javafx.fxml.FXMLLoader;
import javafx.scene.Node;
import javafx.scene.Parent;
import javafx.scene.Scene;
import javafx.scene.control.Alert;
import javafx.stage.Stage;

import java.io.IOException;
import java.util.Stack;

public class NavigationUtils {

    public static void navigateTo(String fxmlPath, String title, Node sourceNode) {
        try {
            FXMLLoader loader = new FXMLLoader(NavigationUtils.class.getResource(fxmlPath));
            Parent root = loader.load();

            Stage stage;
            if (sourceNode != null) {
                stage = (Stage) sourceNode.getScene().getWindow();
            } else {
                stage = new Stage();
            }

            Scene scene = new Scene(root);
            stage.setScene(scene);
            stage.setTitle(title);
            stage.show();
        } catch (IOException e) {
            e.printStackTrace();
            showAlert("Navigation Error", "Failed to load view: " + title + "\nError: " + e.getMessage(), Alert.AlertType.ERROR);
        }
    }


    public static void navigateToPages(ActionEvent event, String fxmlPath, String title) {
        try {
            FXMLLoader loader = new FXMLLoader(NavigationUtils.class.getResource(fxmlPath));
            Parent root = loader.load();

            Stage stage = (Stage) ((Node) event.getSource()).getScene().getWindow();
            Scene scene = new Scene(root);
            stage.setScene(scene);
            stage.setTitle(title);
            stage.show();
        } catch (IOException e) {
            e.printStackTrace();
            showAlert("Navigation Error", "Failed to load view: " + title, Alert.AlertType.ERROR);
        }
    }

    public static <T> T navigateToWithController(String fxmlPath, String title,Node sourceNode) {
        try {
            FXMLLoader loader = new FXMLLoader(NavigationUtils.class.getResource(fxmlPath));
            Parent root = loader.load();

            Stage stage = (Stage) sourceNode.getScene().getWindow();
            Scene scene = new Scene(root);
            stage.setScene(scene);
            stage.setTitle(title);
            stage.show();

            return loader.getController();
        } catch (IOException e) {
            e.printStackTrace();
            showAlert("Navigation Error", "Failed to load view: " + title, Alert.AlertType.ERROR);
            return null;
        }
    }

    public static void showAlert(String title, String message, Alert.AlertType type) {
        Alert alert = new Alert(type);
        alert.setTitle(title);
        alert.setHeaderText(null);
        alert.setContentText(message);
        alert.showAndWait();
    }
}
