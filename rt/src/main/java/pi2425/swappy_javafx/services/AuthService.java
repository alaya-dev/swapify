package pi2425.swappy_javafx.services;

import pi2425.swappy_javafx.entities.User;

import java.sql.SQLException;

public interface AuthService<T> {

    User authenticate(String email, String password) throws SQLException;
    void register(T user);
    void logout();
    boolean isAuthenticated();
    T getCurrentUser();
}
