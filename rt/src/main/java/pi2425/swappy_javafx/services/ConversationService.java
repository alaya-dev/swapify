package pi2425.swappy_javafx.services;

import pi2425.swappy_javafx.entities.Conversation;
import pi2425.swappy_javafx.entities.User;
import pi2425.swappy_javafx.utils.MyDatabase;

import java.sql.*;
import java.util.ArrayList;
import java.util.HashSet;
import java.util.List;
import java.util.Set;

public class ConversationService implements IService<Conversation> {
    private Connection connection;
    public ConversationService() {
        connection = MyDatabase.getInstance().getConnection();
    }

    @Override
    public void ajouter(Conversation conversation) throws SQLException {
        String query = "INSERT INTO conversation DEFAULT VALUES";
        try (PreparedStatement statement = connection.prepareStatement(query, Statement.RETURN_GENERATED_KEYS)) {
            statement.executeUpdate();
            // Get the generated conversation ID
            try (ResultSet generatedKeys = statement.getGeneratedKeys()) {
                if (generatedKeys.next()) {
                    conversation.setId(generatedKeys.getInt(1));
                    // Insert participants into conversation_user table
                    insertParticipants(conversation);
                }
            }
        }
    }

    private void insertParticipants(Conversation conversation) throws SQLException {
        String query = "INSERT INTO conversation_user (conversation_id, user_id) VALUES (?, ?)";
        try (PreparedStatement statement = connection.prepareStatement(query)) {
            for (User user : conversation.getUsers()) {
                statement.setLong(1, conversation.getId());
                statement.setLong(2, user.getId());
                statement.addBatch();
            }
            statement.executeBatch();
        }
    }

    @Override
    public void supprimer(Conversation conversation) throws SQLException {
        String deleteParticipants = "DELETE FROM conversation_user WHERE conversation_id = ?";
        try (PreparedStatement statement = connection.prepareStatement(deleteParticipants)) {
            statement.setLong(1, conversation.getId());
            statement.executeUpdate();
        }
        String deleteConversation = "DELETE FROM conversation WHERE id = ?";
        try (PreparedStatement statement = connection.prepareStatement(deleteConversation)) {
            statement.setLong(1, conversation.getId());
            statement.executeUpdate();
        }
    }

    @Override
    public void modifier(Conversation conversation) throws SQLException {

    }

    @Override
    public List<Conversation> afficher() throws SQLException {
        return List.of();
    }

    @Override
    public Conversation getById(int id) throws SQLException {
        String query = "SELECT * FROM conversation WHERE id = ?";
        try (PreparedStatement statement = connection.prepareStatement(query)) {
            statement.setInt(1, id);
            try (ResultSet resultSet = statement.executeQuery()) {
                if (resultSet.next()) {
                    Conversation conv = new Conversation();
                    conv.setId(resultSet.getInt("id"));
                    return conv;
                }
            }
        }
        return null;
    }


    public Conversation getById(Conversation conversation) throws SQLException {
        String query = "SELECT * FROM conversation WHERE id = ?";
        try (PreparedStatement statement = connection.prepareStatement(query)) {
            statement.setInt(1, conversation.getId());
            try (ResultSet resultSet = statement.executeQuery()) {
                if (resultSet.next()) {
                    Conversation conv = new Conversation();
                    conv.setId(resultSet.getInt("id"));
                    // Load participants
                    loadParticipants(conversation);
                    return conversation;
                }
            }
        }
        return null;
    }

    public void loadParticipants(Conversation conversation) throws SQLException {
        String query = "SELECT user_id FROM conversation_user WHERE conversation_id = ?";
        try (PreparedStatement statement = connection.prepareStatement(query)) {
            statement.setInt(1, conversation.getId());
            try (ResultSet resultSet = statement.executeQuery()) {
                Set<User> participants = new HashSet<>();
                UserService userService = new UserService();
                while (resultSet.next()) {
                    participants.add(userService.getById(resultSet.getInt("user_id")));
                }
                conversation.setUsers(participants);
            }
        }
    }

    public Conversation findConversationBetweenUsers(User user1, User user2) throws SQLException {
        String query = "SELECT c.id FROM conversation c " +
                "JOIN conversation_user cu1 ON c.id = cu1.conversation_id AND cu1.user_id = ? " +
                "JOIN conversation_user cu2 ON c.id = cu2.conversation_id AND cu2.user_id = ? " +
                "GROUP BY c.id HAVING COUNT(DISTINCT cu1.user_id, cu2.user_id) = 2";
        try (PreparedStatement statement = connection.prepareStatement(query)) {
            statement.setLong(1, user1.getId());
            statement.setLong(2, user2.getId());
            try (ResultSet resultSet = statement.executeQuery()) {
                if (resultSet.next()) {
                    return getById(resultSet.getInt("id"));
                }
            }
        }
        return null;
    }

    public Conversation getOrCreateConversation(User user1, User user2) throws SQLException {
        Conversation conversation = findConversationBetweenUsers(user1, user2);
        if (conversation == null) {
            Set<User> participants = new HashSet<>();
            participants.add(user1);
            participants.add(user2);
            conversation = new Conversation(participants);
            ajouter(conversation);
        }
        return conversation;
    }


    public List<Conversation> getConversationsForUser(User user) throws SQLException {
        String query = "SELECT c.id FROM conversation c " +
                "JOIN conversation_user cu ON c.id = cu.conversation_id " +
                "WHERE cu.user_id = ?";
        List<Conversation> conversations = new ArrayList<>();
        try (PreparedStatement statement = connection.prepareStatement(query)) {
            statement.setInt(1, user.getId());
            try (ResultSet resultSet = statement.executeQuery()) {
                System.out.println(resultSet.toString());
                while (resultSet.next()) {
                    //System.out.println(conversations);
                    //System.out.println(resultSet.getInt("id"));
                    int conversationId = resultSet.getInt("id");
                    Conversation conversation = getById(conversationId);
                    if (conversation != null) {
                        conversations.add(conversation);
                        //System.out.println(conversation);
                    }
                }
            }
        }

        return conversations;
    }

}
