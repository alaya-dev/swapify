package pi2425.swappy_javafx.entities;

import java.util.HashSet;
import java.util.Set;

public class Conversation {
    private int id;
    private Set<Message> messages = new HashSet<>();
    private Set<User> users = new HashSet<>();

    public Conversation() {}
    public Conversation(Set<User> users) {
        this.users = users;
    }

    public int getId() {
        return id;
    }

    @Override
    public String toString() {
        return users.stream()
                .map(User::getNom)
                .reduce((a, b) -> a + ", " + b)
                .orElse("Conversation");
    }

    public void setId(int id) {
        this.id = id;
    }

    public Set<Message> getMessages() {
        return messages;
    }

    public void setMessages(Set<Message> messages) {
        this.messages = messages;
    }

    public Set<User> getUsers() {
        return users;
    }

    public void setUsers(Set<User> users) {
        this.users = users;
    }
}
