package pi2425.swappy_javafx.utils;

import javafx.fxml.FXML;
import javafx.scene.image.Image;
import javafx.scene.image.ImageView;

import java.io.File;
import java.nio.file.Path;
import java.nio.file.Paths;

public class LoadExternalImage {

    public static Image loadExternalImage(String path) {

        Path imagePath = Paths.get(System.getProperty("user.dir"), "..", "swapify-dev", "public", "assets", "images", path);
        System.out.println("Looking for image at: " + imagePath);
        String imageUrl = imagePath.toUri().toString();
        Image file = new Image(imageUrl);
        return file;
    }
}
