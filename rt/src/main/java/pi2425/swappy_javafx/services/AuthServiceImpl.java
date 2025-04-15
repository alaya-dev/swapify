package pi2425.swappy_javafx.services;

import pi2425.swappy_javafx.entities.User;
import pi2425.swappy_javafx.utils.MyDatabase;
import pi2425.swappy_javafx.utils.ValidationUtils;

import java.sql.*;

public class AuthServiceImpl implements AuthService {
    private User currentUser;
    Connection connection;
    public AuthServiceImpl() {
        connection= MyDatabase.getInstance().getConnection();

    }
    @Override
    public User authenticate(String email, String password) throws SQLException {
        if (!ValidationUtils.isValidEmail(email) || !ValidationUtils.isValidPassword(password)) {
            return null;
        }

        String req = "SELECT  * FROM user WHERE email = ?";

        try (PreparedStatement pstmt = connection.prepareStatement(req)) {
            pstmt.setString(1, email);
            ResultSet rs = pstmt.executeQuery();

            if (rs.next()) {
                String storedHash = rs.getString("password");
                if (verifyPassword(password, storedHash)) {
                    return  new User(
                            rs.getInt("id"),
                            rs.getString("nom"),
                            rs.getString("email"),
                            rs.getString("prenom"),
                            rs.getDate("date_naissance"),
                            rs.getString("adresse"),
                            rs.getString("tel"),
                            rs.getBoolean("is_verified"),
                            rs.getDate("created_at"),
                            rs.getDate("last_connexion"),
                            storedHash
                    );

                }
            }
        } catch (SQLException e) {
            e.printStackTrace();
            throw e;
        }
        return null;
    }

    @Override
    public void register(Object user) {

    }

    @Override
    public void logout() {

    }

    @Override
    public boolean isAuthenticated() {
        return false;
    }

    @Override
    public Object getCurrentUser() {
        return null;
    }
    private String hashPassword(String password) {
        // In production, use BCrypt or Argon2
        return password; // This is unsafe - just for demonstration
    }

    private boolean verifyPassword(String inputPassword, String storedHash) {
        // In production, use BCrypt.checkpw()
        return inputPassword.equals(storedHash); // This is unsafe - just for demonstration
    }
}
