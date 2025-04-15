package pi2425.swappy_javafx.tests;

import javafx.application.Application;
import javafx.fxml.FXMLLoader;
import javafx.scene.Scene;
import javafx.stage.Stage;

import java.io.IOException;

public class HelloApplication extends Application {

    private static Stage primaryStage;

    @Override
    public void start(Stage stage) throws IOException {
        primaryStage = stage;
        switchScene("/pi2425/swappy_javafx/events/index.fxml"); // Load the first scene
        primaryStage.setTitle("Swapify");
        primaryStage.show();
    }

    public static void switchScene(String fxmlFile) {
        try {
            FXMLLoader loader = new FXMLLoader(HelloApplication.class.getResource(fxmlFile));
            Scene scene = new Scene(loader.load());
            primaryStage.setScene(scene);
        } catch (Exception e) {
            e.printStackTrace();
        }
    }

    public static void main(String[] args) {
        // Suppress FXML version warnings
        System.setProperty("javafx.fxml.autoloader.warning", "false");

        try {
            System.out.println("connection established");
        } catch (Exception e) {
            System.out.println(e.getMessage());
        }

        launch();
    }
}