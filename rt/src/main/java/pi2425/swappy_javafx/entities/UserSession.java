package pi2425.swappy_javafx.entities;

public class UserSession {
    private static UserSession instance;
    private User user;




    public UserSession() {

    }
    private UserSession(User user) {
        this.user = user;
    }

    public static void createSession(User user) {
        if (instance == null) {
            instance = new UserSession(user);
        }
    }

    public static UserSession getInstance() {
        return instance;
    }


    public User getUser() {
        return user;
    }

    public static void clearSession() {
        instance = null;
    }


}
