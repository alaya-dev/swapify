package pi2425.swappy_javafx.entities;


import java.sql.Date;
import java.sql.Time;
import java.sql.Timestamp;

public class Participant_event {

    private int id;
    private User user;
    private Event event;
    private Timestamp inscription_date;

    public Participant_event() {
    }

    public Participant_event(int id, User user, Event event, Timestamp inscription_date) {
        this.id = id;
        this.user = user;
        this.event = event;
        this.inscription_date = inscription_date;
    }

    public Participant_event(User user, Event event, Timestamp inscription_date) {
        this.user = user;
        this.event = event;
        this.inscription_date = inscription_date;
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

    public Timestamp getInscription_date() {
        return inscription_date;
    }

    public void setInscription_date(Timestamp inscription_date) {
        this.inscription_date = inscription_date;
    }

    @Override
    public String toString() {
        return "Participant_event{" +
                "id=" + id +
                ", user=" + user +
                ", event=" + event +
                ", inscription_date=" + inscription_date +
                '}';
    }
}
