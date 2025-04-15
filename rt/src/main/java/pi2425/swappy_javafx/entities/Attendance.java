package pi2425.swappy_javafx.entities;

import java.time.LocalDateTime;

public class Attendance {

    private int id;
    private Session session;
    private Participant_event participant_event;
    private boolean attended;
    private int code;
    private LocalDateTime timestamp;
    public Attendance() {}
    public Attendance(LocalDateTime timestamp,int id, Session session, Participant_event participant_event, boolean attended, int code) {
        this.id = id;
        this.session = session;
        this.participant_event = participant_event;
        this.attended = attended;
        this.code = code;
        this.timestamp = timestamp;
    }

    public Attendance(Session session, Participant_event participant_event, boolean attended, int code, LocalDateTime timestamp) {
        this.session = session;
        this.participant_event = participant_event;
        this.attended = attended;
        this.code = code;
        this.timestamp = timestamp;
    }

    public int getId() {
        return id;
    }

    public Session getSession() {
        return session;
    }

    public void setSession(Session session) {
        this.session = session;
    }

    public Participant_event getParticipant_event() {
        return participant_event;
    }

    public void setParticipant_event(Participant_event participant_event) {
        this.participant_event = participant_event;
    }

    public boolean isAttended() {
        return attended;
    }

    public void setAttended(boolean attended) {
        this.attended = attended;
    }

    public int getCode() {
        return code;
    }

    public void setCode(int code) {
        this.code = code;
    }

    public LocalDateTime getTimestamp() {
        return timestamp;
    }

    public void setTimestamp(LocalDateTime timestamp) {
        this.timestamp = timestamp;
    }

    @Override
    public String toString() {
        return "Attendance{" +
                "id=" + id +
                ", session=" + session +
                ", participant_event=" + participant_event +
                ", attended=" + attended +
                ", code=" + code +
                ", timestamp=" + timestamp +
                '}';
    }
}
