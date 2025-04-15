package pi2425.swappy_javafx.utils;

import javafx.scene.image.Image;
import javafx.scene.image.ImageView;
import javafx.stage.FileChooser;
import javafx.stage.Stage;

import java.io.File;
import java.io.IOException;
import java.nio.file.Files;
import java.nio.file.StandardCopyOption;
import java.util.UUID;

public class UploadImageUtil {



    public static String uploadImage(Stage stage, String IMAGE_STORAGE_PATH) {
        FileChooser fileChooser = new FileChooser();
        fileChooser.setTitle("Select Image");
        fileChooser.getExtensionFilters().add(
                new FileChooser.ExtensionFilter("Image Files", "*.png", "*.jpg", "*.jpeg")
        );

        File selectedFile = fileChooser.showOpenDialog(stage);
        if (selectedFile != null) {
            try {
                // Ensure the directory exists
                File storageDir = new File(IMAGE_STORAGE_PATH);
                if (!storageDir.exists()) {
                    storageDir.mkdirs();
                }

                // Generate a unique file name
                String fileExtension = selectedFile.getName().substring(selectedFile.getName().lastIndexOf("."));
                String uniqueFileName = UUID.randomUUID().toString() + fileExtension;

                // Save the file in the external directory
                File savedFile = new File(storageDir, uniqueFileName);
                Files.copy(selectedFile.toPath(), savedFile.toPath(), StandardCopyOption.REPLACE_EXISTING);

                // Return the saved file name (for database storage)
                return uniqueFileName;

            } catch (IOException e) {
                e.printStackTrace();
                return null;
            }
        }
        return null; // No file selected
    }
}
