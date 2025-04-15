package pi2425.swappy_javafx.entities;


import java.sql.Date;

public class Event {

    private int id;
    private User orgniser;
    private String title;
    private String description;
    private Date date_debut;
    private Date date_fin;
    private int max_participants;
    private String status;
    private String image;
    private transient int sessionCount;

    public int getSessionCount() {
        return sessionCount;
    }

    public void setSessionCount(int sessionCount) {
        this.sessionCount = sessionCount;
    }

    public void setId(int id) {
        this.id = id;
    }

    public Event(int id, User orgniser, String title, String description, Date date_debut, Date date_fin, int max_participants, String status, String image) {
        this.id = id;
        this.orgniser = orgniser;
        this.title = title;
        this.description = description;
        this.date_debut = date_debut;
        this.date_fin = date_fin;
        this.max_participants = max_participants;
        this.status = status;
        this.image = image;
    }

    public Event(User orgniser, String title, String description, Date date_debut, Date date_fin, int max_participants, String status, String image) {
        this.orgniser = orgniser;
        this.title = title;
        this.description = description;
        this.date_debut = date_debut;
        this.date_fin = date_fin;
        this.max_participants = max_participants;
        this.status = status;
        this.image = image;
    }

    public Event() {

    }

    public int getId() {
        return id;
    }

    public User getOrgniser() {
        return orgniser;
    }

    public void setOrgniser(User orgniser) {
        this.orgniser = orgniser;
    }

    public String getTitle() {
        return title;
    }

    public void setTitle(String title) {
        this.title = title;
    }

    public String getDescription() {
        return description;
    }

    public void setDescription(String description) {
        this.description = description;
    }

    public Date getDate_debut() {
        return date_debut;
    }

    public void setDate_debut(Date date_debut) {
        this.date_debut = date_debut;
    }

    public Date getDate_fin() {
        return date_fin;
    }

    public void setDate_fin(Date date_fin) {
        this.date_fin = date_fin;
    }

    public int getMax_participants() {
        return max_participants;
    }

    public void setMax_participants(int max_participants) {
        this.max_participants = max_participants;
    }

    public String getStatus() {
        return status;
    }

    public void setStatus(String status) {
        this.status = status;
    }

    public String getImage() {
        return image;
    }

    public void setImage(String image) {
        this.image = image;
    }

    @Override
    public String toString() {
        return "Event{" +
                "id=" + id +
                ", orgniser=" + orgniser +
                ", title='" + title + '\'' +
                ", description='" + description + '\'' +
                ", date_debut=" + date_debut +
                ", date_fin=" + date_fin +
                ", max_participants=" + max_participants +
                ", status='" + status + '\'' +
                ", image='" + image + '\'' +
                '}';
    }
}
