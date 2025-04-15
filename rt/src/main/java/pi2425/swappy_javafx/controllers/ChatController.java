package pi2425.swappy_javafx.controllers;

import com.pusher.client.Pusher;
import com.pusher.client.PusherOptions;
import com.pusher.client.channel.Channel;
import com.pusher.client.connection.ConnectionEventListener;
import com.pusher.client.connection.ConnectionStateChange;
import javafx.application.Platform;
import javafx.fxml.FXML;
import javafx.geometry.Insets;
import javafx.geometry.Pos;
import javafx.scene.control.*;
import javafx.scene.layout.*;
import javafx.scene.shape.Circle;
import javafx.scene.text.Text;
import org.json.JSONObject;
import pi2425.swappy_javafx.entities.Message;
import pi2425.swappy_javafx.entities.User;
import pi2425.swappy_javafx.entities.UserSession;
import pi2425.swappy_javafx.entities.Conversation;
import pi2425.swappy_javafx.services.ConversationService;
import pi2425.swappy_javafx.services.MessageService;

import java.net.URI;
import java.net.http.HttpClient;
import java.net.http.HttpRequest;
import java.net.http.HttpResponse;
import java.sql.SQLException;
import java.time.Instant;
import java.time.LocalDateTime;
import java.time.ZoneId;
import java.time.format.DateTimeFormatter;
import java.util.List;

public class ChatController {
    @FXML private VBox conversationList;
    @FXML private TextField searchBar;
    @FXML private Button backButton;
    @FXML private VBox messagesContainer;
    @FXML private TextField messageInput;
    @FXML private Button sendButton;
    @FXML private Circle participantAvatar;
    @FXML private Label conversationTitle;
    @FXML private ScrollPane messageArea;

    private static final String PUSHER_APP_KEY = "7f4f9c9d2b396ad6ec87";
    private static final String PUSHER_CLUSTER = "eu";
    private static final String API_ENDPOINT = "http://localhost:8000/api/messages";

    private final User currentUser;
    private Pusher pusher;
    private Channel channel;
    private final HttpClient httpClient;
    private Conversation currentConversation;

    private ConversationService conversationService = new ConversationService();
    private MessageService messageService = new MessageService();

    public ChatController() {
        this.currentUser = UserSession.getInstance().getUser();
        this.httpClient = HttpClient.newBuilder().build();
    }

    @FXML
    public void initialize() {
        initializeUI();
        initializePusher();
        loadConversations();
    }

    private void initializeUI() {
        // Message input handling
        messageInput.setOnAction(event -> sendMessage());
        sendButton.setOnAction(event -> sendMessage());

        // Auto-scroll to bottom when new messages arrive
        messagesContainer.heightProperty().addListener((obs, old, val) -> 
            messageArea.setVvalue(1.0));

        // Search functionality
        searchBar.textProperty().addListener((obs, old, val) -> 
            filterConversations(val));
    }

    private void initializePusher() {
        try {
            PusherOptions options = new PusherOptions().setCluster(PUSHER_CLUSTER);
            pusher = new Pusher(PUSHER_APP_KEY, options);

            pusher.connect(new ConnectionEventListener() {
                @Override
                public void onConnectionStateChange(ConnectionStateChange change) {
                    System.out.println("State changed from " + change.getPreviousState() + 
                                     " to " + change.getCurrentState());
                }

                @Override
                public void onError(String message, String code, Exception e) {
                    System.out.println("Error: " + message);
                }
            });

            channel = pusher.subscribe("chat-channel");
            channel.bind("new-message", event -> {
                Platform.runLater(() -> handleIncomingMessage(event.getData()));
            });

        } catch (Exception e) {
            showError("Failed to initialize chat: " + e.getMessage());
        }
    }

    private void handleIncomingMessage(String eventData) {
        try {
            JSONObject data = new JSONObject(eventData);
            String message = data.getString("message");
            String sender = data.getString("sender");
            
            addMessageToUI(message, sender.equals(currentUser.getNom()));
        } catch (Exception e) {
            showError("Failed to process message: " + e.getMessage());
        }
    }

    @FXML
    private void sendMessage() {
        String messageText = messageInput.getText().trim();
        if (messageText.isEmpty()) return;

        JSONObject payload = new JSONObject()
            .put("message", messageText)
            .put("sender", currentUser.getNom());

        HttpRequest request = HttpRequest.newBuilder()
            .uri(URI.create(API_ENDPOINT))
            .header("Content-Type", "application/json")
            .POST(HttpRequest.BodyPublishers.ofString(payload.toString()))
            .build();

        httpClient.sendAsync(request, HttpResponse.BodyHandlers.ofString())
            .thenAccept(response -> {
                if (response.statusCode() == 201) {
                    Platform.runLater(() -> {
                        addMessageToUI(messageText, true);
                        messageInput.clear();
                    });
                } else {
                    showError("Failed to send message");
                }
            })
            .exceptionally(e -> {
                showError("Network error: " + e.getMessage());
                return null;
            });
    }

    private void addMessageToUI(String messageText, boolean isSentByMe) {
        VBox messageContainer = new VBox(5);
        messageContainer.getStyleClass().add("message-container");
        
        HBox messageBox = new HBox(10);
        messageBox.getStyleClass().add("message-box");
        
        Label messageLabel = new Label(messageText);
        messageLabel.setWrapText(true);
        messageLabel.getStyleClass().add("message-text");
        
        Label timeLabel = new Label(LocalDateTime.now().format(
            DateTimeFormatter.ofPattern("HH:mm")));
        timeLabel.getStyleClass().add("message-time");

        VBox textContainer = new VBox(2);
        textContainer.getChildren().addAll(messageLabel, timeLabel);

        messageBox.getChildren().add(textContainer);
        messageContainer.getChildren().add(messageBox);

        if (isSentByMe) {
            messageContainer.setAlignment(Pos.CENTER_RIGHT);
            messageBox.getStyleClass().add("sent-message");
        } else {
            messageContainer.setAlignment(Pos.CENTER_LEFT);
            messageBox.getStyleClass().add("received-message");
        }

        messagesContainer.getChildren().add(messageContainer);
    }

    private void loadConversations() {
        try {
            List<Conversation> conversations = conversationService.getConversationsForUser(currentUser);
            conversationList.getChildren().clear();
            
            for (Conversation conversation : conversations) {
                // Load participants and messages for each conversation
                conversationService.loadParticipants(conversation);
                VBox conversationItem = createConversationItem(conversation);
                conversationList.getChildren().add(conversationItem);
            }
        } catch (SQLException e) {
            showError("Failed to load conversations: " + e.getMessage());
        }
    }

    private VBox createConversationItem(Conversation conversation) {
        VBox item = new VBox(5);
        item.getStyleClass().add("conversation-item");
        item.setPadding(new Insets(10));
        
        // Get the other participant's name (excluding current user)
        String participantName = conversation.getUsers().stream()
            .filter(user -> user.getId() != currentUser.getId())
            .map(User::getNom)
            .findFirst()
            .orElse("Unknown User");
        
        // User name
        Label nameLabel = new Label(participantName);
        nameLabel.getStyleClass().add("conversation-name");
        
        // Get last message if any exists
        List<Message> messages;
        String lastMessageContent = "No messages yet";
        String timeText = "";
        
        try {
            messages = messageService.getMessagesByConversation(conversation);
            if (!messages.isEmpty()) {
                Message lastMessage = messages.get(messages.size() - 1);
                lastMessageContent = lastMessage.getContent();
                timeText = formatTime(lastMessage.getCreatedAt());
            }
        } catch (SQLException e) {
            lastMessageContent = "Error loading messages";
        }
        
        Label lastMessageLabel = new Label(lastMessageContent);
        lastMessageLabel.getStyleClass().add("conversation-last-message");
        lastMessageLabel.setWrapText(true);
        
        Label timeLabel = new Label(timeText);
        timeLabel.getStyleClass().add("conversation-time");
        
        item.getChildren().addAll(nameLabel, lastMessageLabel, timeLabel);
        
        // Add click handler
        item.setOnMouseClicked(event -> {
            currentConversation = conversation;
            conversationTitle.setText(participantName);
            loadMessages(conversation);
            
            // Add selected style
            conversationList.getChildren().forEach(node -> 
                node.getStyleClass().remove("conversation-selected"));
            item.getStyleClass().add("conversation-selected");
        });
        
        return item;
    }

    private void loadMessages(Conversation conversation) {
        try {
            List<Message> messages = messageService.getMessagesByConversation(conversation);
            messagesContainer.getChildren().clear();
            
            for (Message message : messages) {
                boolean isSentByMe = message.getAuthor().getId() == currentUser.getId();
                addMessageToUI(message.getContent(), isSentByMe);
            }
        } catch (SQLException e) {
            showError("Failed to load messages: " + e.getMessage());
        }
    }

    private String formatTime(Instant time) {
        LocalDateTime dateTime = LocalDateTime.ofInstant(time, ZoneId.systemDefault());
        LocalDateTime now = LocalDateTime.now();
        
        if (dateTime.toLocalDate().equals(now.toLocalDate())) {
            return dateTime.format(DateTimeFormatter.ofPattern("HH:mm"));
        } else {
            return dateTime.format(DateTimeFormatter.ofPattern("dd/MM/yyyy"));
        }
    }

    private void filterConversations(String searchText) {
        // Implement conversation filtering logic here
    }

    private void showError(String message) {
        Platform.runLater(() -> {
            Alert alert = new Alert(Alert.AlertType.ERROR);
            alert.setTitle("Error");
            alert.setContentText(message);
            alert.show();
        });
    }

    public void cleanup() {
        if (pusher != null) {
            pusher.disconnect();
        }
    } 
}