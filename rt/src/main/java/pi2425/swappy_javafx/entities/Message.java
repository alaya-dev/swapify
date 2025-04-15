package pi2425.swappy_javafx.entities;

import java.time.Instant;

public class Message {
    private Long id;
    private String content;
    private User author;
    private Instant createdAt;
    private Conversation conversation;

    public Message() {
        this.createdAt = Instant.now();
    }
    public Message(String content, User author, Conversation conversation) {
        this();
        this.content = content;
        this.author = author;
        this.conversation = conversation;
    }

    public Long getId() {
        return id;
    }

    public void setId(Long id) {
        this.id = id;
    }

    public String getContent() {
        return content;
    }

    public void setContent(String content) {
        this.content = content;
    }

    public User getAuthor() {
        return author;
    }

    public void setAuthor(User author) {
        this.author = author;
    }

    public Instant getCreatedAt() {
        return createdAt;
    }

    public void setCreatedAt(Instant createdAt) {
        this.createdAt = createdAt;
    }

    public Conversation getConversation() {
        return conversation;
    }

    public void setConversation(Conversation conversation) {
        this.conversation = conversation;
    }

}
