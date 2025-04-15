package pi2425.swappy_javafx.entities;

import java.util.Date;

public class Certificate {

    private int id;
    private User user;
    private Event event;
    private Date date_acquisition;

    public Certificate() {
    }
    public Certificate(int id, User user, Event event, Date date_acquisition) {
        this.id = id;
        this.user = user;
        this.event = event;
        this.date_acquisition = date_acquisition;
    }

    public Certificate(User user, Event event, Date date_acquisition) {
        this.user = user;
        this.event = event;
        this.date_acquisition = date_acquisition;
    }

    public int getId() {
        return id;
    }
    public User getUser() {
        return user;
    }

    public void setUser(User user) {
        this.user = user;
    }

    public Event getEvent() {
        return event;
    }

    public void setEvent(Event event) {
        this.event = event;
    }

    public Date getDate_acquisition() {
        return date_acquisition;
    }

    public void setDate_acquisition(Date date_acquisition) {
        this.date_acquisition = date_acquisition;
    }
}
