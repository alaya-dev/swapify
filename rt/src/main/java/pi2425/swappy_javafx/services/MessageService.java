package pi2425.swappy_javafx.services;

import pi2425.swappy_javafx.entities.Conversation;
import pi2425.swappy_javafx.entities.Message;
import pi2425.swappy_javafx.entities.User;
import pi2425.swappy_javafx.entities.UserSession;
import pi2425.swappy_javafx.utils.MyDatabase;

import java.sql.*;
import java.util.ArrayList;
import java.util.List;

public class MessageService implements IService <Message> {

    private Connection connection;
    private ConversationService conversationService = new ConversationService();
    private final UserService userService = new UserService();

    public MessageService() {
        connection = MyDatabase.getInstance().getConnection();
    }

    public void saveMessage(Conversation conversationId, String content) {
        User author = UserSession.getInstance().getUser();
        Conversation conversation = null;
        try {
            conversation = conversationService.getById(conversationId);
            Message message = new Message(content, author, conversation);
            this.ajouter(message);
        } catch (SQLException e) {
            throw new RuntimeException(e);
        }
    }

    public User getUserById(Long id) throws SQLException {
        return userService.getById(id.intValue());
    }

    @Override
    public void ajouter(Message message) throws SQLException {
        String query = "INSERT INTO message (content, author_id, created_at, conversation_id) VALUES (?, ?, ?, ?)";
        try (PreparedStatement statement = connection.prepareStatement(query, Statement.RETURN_GENERATED_KEYS)) {
            statement.setString(1, message.getContent());
            statement.setLong(2, message.getAuthor().getId());
            statement.setTimestamp(3, Timestamp.from(message.getCreatedAt()));
            statement.setLong(4, message.getConversation().getId());
            statement.executeUpdate();

            // Get the generated message ID
            try (ResultSet generatedKeys = statement.getGeneratedKeys()) {
                if (generatedKeys.next()) {
                    message.setId(generatedKeys.getLong(1));
                }
            }
        }
    }

    @Override
    public void supprimer(Message message) throws SQLException {

    }

    @Override
    public void modifier(Message message) throws SQLException {

    }

    @Override
    public List<Message> afficher() throws SQLException {
        return List.of();
    }

    @Override
    public Message getById(int id) throws SQLException {
        return null;
    }

    public List<Message> getMessagesByConversation(Conversation conversation) throws SQLException {
        List<Message> messages = new ArrayList<>();
        String query = "SELECT * FROM message WHERE conversation_id = ? ORDER BY created_at ASC";
        try (PreparedStatement statement = connection.prepareStatement(query)) {
            statement.setLong(1, conversation.getId());
            try (ResultSet resultSet = statement.executeQuery()) {
                while (resultSet.next()) {
                    Message message = new Message();
                    message.setId(resultSet.getLong("id"));
                    message.setContent(resultSet.getString("content"));
                    UserService userService = new UserService();
                    message.setAuthor(userService.getById(resultSet.getInt("author_id")));
                    message.setCreatedAt(resultSet.getTimestamp("created_at").toInstant());
                    message.setConversation(conversation);
                    messages.add(message);
                }
            }
        }
        return messages;
    }
}

