package pi2425.swappy_javafx.services;

import pi2425.swappy_javafx.entities.Event;
import pi2425.swappy_javafx.entities.Session;
import pi2425.swappy_javafx.entities.User;
import pi2425.swappy_javafx.utils.MyDatabase;

import java.sql.*;
import java.time.LocalDateTime;
import java.util.ArrayList;
import java.util.List;

public class SessionService implements IService<Session> {

    Connection connection;
    UserService userService;
    public SessionService() {
        connection= MyDatabase.getInstance().getConnection();
        userService= new UserService();
    }

    @Override
    public void ajouter(Session session) throws SQLException {
        String req = "INSERT INTO session (event_id, start_hour, end_hour, type_session, objective, meeting_started) " +
                "VALUES (?, ?, ?, ?, ?, ?)";
        PreparedStatement ps = connection.prepareStatement(req);
        ps.setInt(1, session.getEvent_id());
        ps.setTimestamp(2, Timestamp.valueOf(session.getStart_hour())); // Convert LocalDateTime to Timestamp
        ps.setTimestamp(3, Timestamp.valueOf(session.getEnd_hour()));   // Convert LocalDateTime to Timestamp
        ps.setString(4, session.getType_session().toString());
        ps.setString(5, session.getObjective());
        ps.setBoolean(6, false);
        ps.executeUpdate();
    }

    @Override
    public void supprimer(Session session) throws SQLException {
        String sql = "DELETE FROM session WHERE id = ?";
        PreparedStatement ps = connection.prepareStatement(sql);
        ps.setInt(1, session.getId());
        ps.executeUpdate();
    }

    @Override
    public void modifier(Session session) throws SQLException {
        String sql = "UPDATE session SET objective = ?, type_session = ?, start_hour = ?, end_hour = ? WHERE id = ?";
        try (PreparedStatement statement = connection.prepareStatement(sql)) {
            statement.setString(1, session.getObjective());
            statement.setString(2, session.getType_session());
            statement.setTimestamp(3, Timestamp.valueOf(session.getStart_hour()));
            statement.setTimestamp(4, Timestamp.valueOf(session.getEnd_hour()));
            statement.setInt(5, session.getId());
            statement.executeUpdate();
        }
    }

    @Override
    public List<Session> afficher() throws SQLException {
        return List.of();
    }

    @Override
    public Session getById(int id) throws SQLException {
        return null;
    }

    public List<Session> getSessionByEventId(int event_id) throws SQLException {
        List<Session> sessions = new ArrayList<>();

        String req = "SELECT s.*,e.orgniser_id  FROM session s JOIN event e on s.event_id=e.id WHERE event_id = ?";
        PreparedStatement ps = connection.prepareStatement(req);
        ps.setInt(1, event_id);
        ResultSet rs = ps.executeQuery();
        while (rs.next()) {
            Session session = new Session();
            session.setId(rs.getInt("id"));
            session.setObjective(rs.getString("objective"));
            session.setType_session(rs.getString("type_session"));
            session.setStart_hour(rs.getTimestamp("start_hour").toLocalDateTime());
            session.setEnd_hour(rs.getTimestamp("end_hour").toLocalDateTime());
            session.setMeeting_started(rs.getBoolean("meeting_started"));
            session.setEvent_id(rs.getInt("event_id"));


            Event event = new Event();
            int userId = rs.getInt("orgniser_id");
            User orgniser=userService.getById(userId);
            event.setOrgniser(orgniser);
            session.setEvent(event);
            sessions.add(session);

        }
        return sessions;

    }






}
