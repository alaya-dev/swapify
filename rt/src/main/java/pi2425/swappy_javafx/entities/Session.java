package pi2425.swappy_javafx.entities;

import java.time.LocalDateTime;

public class Session {

    private int id;
    private int event_id;
    private LocalDateTime start_hour;
    private LocalDateTime end_hour;
    private String objective;
    private String type_session;
    private boolean meeting_started;
    private Event event;


    public Session() {
    }

    public Session(Event event,int id, int event_id, LocalDateTime start_hour, LocalDateTime end_hour, String objective, String type_session, boolean meeting_started) {
        this.event=event;
        this.id = id;
        this.event_id = event_id;
        this.start_hour = start_hour;
        this.end_hour = end_hour;
        this.objective = objective;
        this.type_session = type_session;
        this.meeting_started = meeting_started;
    }

    public Session(Event event,int event_id, LocalDateTime start_hour, LocalDateTime end_hour, String objective, String type_session, boolean meeting_started) {
        this.event=event;
        this.event_id = event_id;
        this.start_hour = start_hour;
        this.end_hour = end_hour;
        this.objective = objective;
        this.type_session = type_session;
        this.meeting_started = meeting_started;
    }

    public int getId() {
        return id;
    }

    public Event getEvent() {
        return event;
    }

    public void setEvent(Event event) {
        this.event = event;
    }

    public void setId(int id) {
        this.id = id;
    }

    public void setEvent_id(int event_id) {
        this.event_id = event_id;
    }

    public int getEvent_id() {
        return event_id;
    }

    public void setEventId(int event_id) {
        this.event_id = event_id;
    }

    public LocalDateTime getStart_hour() {
        return start_hour;
    }

    public void setStart_hour(LocalDateTime start_hour) {
        this.start_hour = start_hour;
    }

    public LocalDateTime getEnd_hour() {
        return end_hour;
    }

    public void setEnd_hour(LocalDateTime end_hour) {
        this.end_hour = end_hour;
    }

    public String getObjective() {
        return objective;
    }

    public void setObjective(String objective) {
        this.objective = objective;
    }

    public String getType_session() {
        return type_session;
    }

    public void setType_session(String type_session) {
        this.type_session = type_session;
    }

    public boolean isMeeting_started() {
        return meeting_started;
    }

    public void setMeeting_started(boolean meeting_started) {
        this.meeting_started = meeting_started;
    }

    @Override
    public String toString() {
        return "Session{" +
                "id=" + id +
                ", event=" + event_id +
                ", start_hour=" + start_hour +
                ", end_hour=" + end_hour +
                ", objective='" + objective + '\'' +
                ", type_session=" + type_session +
                ", meeting_started=" + meeting_started +
                '}';
    }
}
