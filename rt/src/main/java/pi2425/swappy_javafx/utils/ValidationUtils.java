package pi2425.swappy_javafx.utils;

import javafx.scene.control.Label;

import java.time.LocalDate;

public class ValidationUtils {

    public static boolean isValidEmail(String email) {
        return email != null && email.matches("^[\\w-.]+@([\\w-]+\\.)+[\\w-]{2,4}$");
    }

    public static boolean isValidPassword(String password) {
        return password != null && password.length() >= 8;
    }

    public static void showError(Label label, String message) {
        label.setText(message);
        label.setStyle("-fx-text-fill: #d32f2f; -fx-font-size: 12px;");
        label.setVisible(true);
    }

    public static void clearError(Label label) {
        label.setText("");
        label.setVisible(false);
    }

    public static boolean isDateAfterOrEqual(LocalDate date1, LocalDate date2) {
        return date1.isAfter(date2) || date1.isEqual(date2);
    }
}
