package pi2425.swappy_javafx.services;

import javafx.scene.control.Alert;
import pi2425.swappy_javafx.entities.Event;
import pi2425.swappy_javafx.entities.User;
import pi2425.swappy_javafx.entities.UserSession;
import pi2425.swappy_javafx.utils.MyDatabase;

import java.sql.*;
import java.util.ArrayList;
import java.util.List;


public class EventService implements IService<Event> {

    Connection connection;
    UserService userService;
    public EventService() {
        connection= MyDatabase.getInstance().getConnection();
        this.userService = new UserService();
    }


    public int ajouterEvent(Event event) throws SQLException {

        int organizerId = UserSession.getInstance().getUser().getId();
        String sql = "INSERT INTO event (orgniser_id, title, description, date_debut, date_fin, max_participant, status, image) " +
                "VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        PreparedStatement preparedStatement = connection.prepareStatement(sql, Statement.RETURN_GENERATED_KEYS);
            preparedStatement.setInt(1, organizerId); // Organizer ID
            preparedStatement.setString(2, event.getTitle());
            preparedStatement.setString(3, event.getDescription());
            preparedStatement.setDate(4, event.getDate_debut());
            preparedStatement.setDate(5, event.getDate_fin());
            preparedStatement.setInt(6, event.getMax_participants());
            preparedStatement.setString(7, event.getStatus());
            preparedStatement.setString(8, event.getImage());
            preparedStatement.executeUpdate();
            ResultSet resultSet = preparedStatement.getGeneratedKeys();
            if (resultSet.next()) {
                int id = resultSet.getInt(1);
                return id;
            }else {
                throw new SQLException("Failed to get generated ID for event");
            }







    }

    @Override
    public void ajouter(Event event) throws SQLException {


    }

    @Override
    public void supprimer(Event event) throws SQLException {
        String deleteSessions = "DELETE FROM session WHERE event_id = ?";
        try (PreparedStatement sessionStmt = connection.prepareStatement(deleteSessions)) {
            sessionStmt.setInt(1, event.getId());
            sessionStmt.executeUpdate();
        }
        String req="DELETE FROM event WHERE id=?";
        PreparedStatement preparedStatement = connection.prepareStatement(req);
        preparedStatement.setInt(1, event.getId());
        preparedStatement.executeUpdate();
    }

    @Override
    public void modifier(Event event) throws SQLException {
        String sql="UPDATE event set orgniser_id=?,title=?,description=?,date_debut=?,date_fin=?,max_participant=?,status=?,image=? where id=?";
        User user=UserSession.getInstance().getUser();
        PreparedStatement preparedStatement = connection.prepareStatement(sql,Statement.RETURN_GENERATED_KEYS);
        preparedStatement.setInt(1, user.getId());
        preparedStatement.setString(2, event.getTitle());
        preparedStatement.setString(3, event.getDescription());
        preparedStatement.setDate(4, event.getDate_debut());
        preparedStatement.setDate(5, event.getDate_fin());
        preparedStatement.setInt(6, event.getMax_participants());
        preparedStatement.setString(7, event.getStatus());
        preparedStatement.setString(8, event.getImage());
        preparedStatement.setInt(9, event.getId());
        preparedStatement.executeUpdate();
    }




    public List<Event> afficher() throws SQLException {
        List<Event> events = new ArrayList<>();
        String req = "SELECT e.*, COUNT(s.id) as session_count " +
                "FROM event e LEFT JOIN session s ON e.id = s.event_id " +
                "GROUP BY e.id";  // Removed the WHERE clause

        try (PreparedStatement preparedStatement = connection.prepareStatement(req)) {
            ResultSet resultSet = preparedStatement.executeQuery();
            while (resultSet.next()) {
                Event event = new Event();
                event.setId(resultSet.getInt("id"));

                int userId = resultSet.getInt("orgniser_id");
                User user = userService.getById(userId);
                event.setOrgniser(user);
                event.setTitle(resultSet.getString("title"));
                event.setDescription(resultSet.getString("description"));
                event.setDate_debut(resultSet.getDate("date_debut"));
                event.setDate_fin(resultSet.getDate("date_fin"));
                event.setMax_participants(resultSet.getInt("max_participant"));
                event.setStatus(resultSet.getString("status"));
                event.setImage(resultSet.getString("image"));
                event.setSessionCount(resultSet.getInt("session_count"));
                events.add(event);
            }
        }
        return events;
    }

    public List<Event> getMesEvent() throws SQLException {
        int organizerId = UserSession.getInstance().getUser().getId();

        List<Event> events = new ArrayList<>();
        String req = "SELECT e.*, COUNT(s.id) AS session_count " +
                "FROM event e LEFT JOIN session s ON e.id = s.event_id " +
                "WHERE e.orgniser_id = ? " +
                "GROUP BY e.id";


        PreparedStatement preparedStatement = connection.prepareStatement(req);
        preparedStatement.setInt(1, organizerId);
        ResultSet resultSet = preparedStatement.executeQuery();
        while (resultSet.next()) {
            Event event = new Event();
            event.setId(resultSet.getInt("id"));
            User user=userService.getById(organizerId);
            event.setOrgniser(user);
            event.setTitle(resultSet.getString("title"));
            event.setDescription(resultSet.getString("description"));
            event.setDate_debut(resultSet.getDate("date_debut"));
            event.setDate_fin(resultSet.getDate("date_fin"));
            event.setMax_participants(resultSet.getInt("max_participant"));
            event.setStatus(resultSet.getString("status"));
            event.setImage(resultSet.getString("image"));
            event.setSessionCount(resultSet.getInt("session_count"));
            events.add(event);

        }
        return events;
    }


    @Override
    public Event getById(int id) throws SQLException {

        String sql = "SELECT * FROM event WHERE id = ?";

        PreparedStatement preparedStatement = connection.prepareStatement(sql);
        preparedStatement.setInt(1, id);
        ResultSet resultSet = preparedStatement.executeQuery();
        if (resultSet.next()) {
            Event event = new Event();
            event.setId(resultSet.getInt("id"));
            int userId= resultSet.getInt("orgniser_id");
            System.out.println(userId);
            User user=userService.getById(userId);
            event.setOrgniser(user);
            event.setTitle(resultSet.getString("title"));
            event.setDescription(resultSet.getString("description"));
            event.setDate_debut(resultSet.getDate("date_debut"));
            event.setDate_fin(resultSet.getDate("date_fin"));
            event.setMax_participants(resultSet.getInt("max_participant"));
            event.setStatus(resultSet.getString("status"));
            event.setImage(resultSet.getString("image"));

            return event;
        }

        return  null;
    }

    public List<Event>getAllAcceptedEvents() throws SQLException {
        List<Event> events = new ArrayList<>();
        String sql = "SELECT * FROM event WHERE status = 'Acceptee'";
        PreparedStatement preparedStatement = connection.prepareStatement(sql);

        ResultSet resultSet = preparedStatement.executeQuery();
        while (resultSet.next()) {
            Event event = new Event();
            event.setId(resultSet.getInt("id"));
            int userId = resultSet.getInt("orgniser_id");
            User user=userService.getById(userId);
            event.setOrgniser(user);
            event.setTitle(resultSet.getString("title"));
            event.setDescription(resultSet.getString("description"));
            event.setDate_debut(resultSet.getDate("date_debut"));
            event.setDate_fin(resultSet.getDate("date_fin"));
            event.setMax_participants(resultSet.getInt("max_participant"));
            event.setStatus(resultSet.getString("status"));
            event.setImage(resultSet.getString("image"));
            events.add(event);

        }
        return events;
    }

    public List<Event>getUpcomingEvents() throws SQLException {
        List<Event> events = new ArrayList<>();
        String sql = "SELECT * FROM event " +
                "WHERE status = 'Acceptee' " +
                "AND date_debut >= CURDATE() " +
                "ORDER BY date_debut ASC " +
                "LIMIT 3";


            PreparedStatement preparedStatement = connection.prepareStatement(sql);
             ResultSet rs = preparedStatement.executeQuery();

            while (rs.next()) {
                Event event = new Event();
                event.setId(rs.getInt("id"));
                int userId = rs.getInt("orgniser_id");
                User user=userService.getById(userId);
                event.setOrgniser(user);
                event.setTitle(rs.getString("title"));
                event.setDescription(rs.getString("description"));
                event.setDate_debut(rs.getDate("date_debut"));
                event.setDate_fin(rs.getDate("date_fin"));
                event.setMax_participants(rs.getInt("max_participant"));
                event.setStatus(rs.getString("status"));
                event.setImage(rs.getString("image"));


                events.add(event);
            }


        return events;
    }


    public void participerEvent(Event event) throws SQLException {
        int userId = UserSession.getInstance().getUser().getId();
        String sql = "INSERT INTO participant_event (user_id, event_id, inscription_date) VALUES (?, ?, current_timestamp)";

        PreparedStatement preparedStatement = connection.prepareStatement(sql);
        if(userId == event.getOrgniser().getId()) {
            Alert alert = new Alert(Alert.AlertType.ERROR);
            alert.setTitle("Unauthorized");
            alert.setHeaderText("particiaption impossible");
            alert.setContentText("Vous ne pouvez pas joindre cette évenement puisque vous étes dèja l'organisateur de ce dernier");
            alert.showAndWait();

        }
        preparedStatement.setInt(1, userId);
        preparedStatement.setInt(2, event.getId());
        Timestamp currentTimestamp = new Timestamp(System.currentTimeMillis());

        preparedStatement.executeUpdate();


        String Sql2="UPDATE event set max_participant=? where id=? ";
        PreparedStatement preparedStatement2 = connection.prepareStatement(Sql2);
        preparedStatement2.setInt(1,event.getMax_participants());
        preparedStatement2.setInt(2,event.getId());
        preparedStatement2.executeUpdate();


    }


    public boolean hasConflictingEvents(int userId, Event newEvent) throws SQLException {

        String sql = "SELECT e.* FROM events e JOIN participants p ON e.id = p.event_id " +
                "WHERE p.user_id = ? AND e.date_debut < ? AND e.date_fin > ?";
        try (PreparedStatement stmt = connection.prepareStatement(sql)) {
            stmt.setInt(1, userId);
            stmt.setDate(2, newEvent.getDate_fin());
            stmt.setDate(3, newEvent.getDate_debut());
            ResultSet rs = stmt.executeQuery();
            return rs.next(); // Returns true if there are conflicting events
        }
        catch (SQLException e) {
            return false;
        }
    }


    public List<Event> getParticipations() throws SQLException {
        User user = UserSession.getInstance().getUser();
        List<Event> events = new ArrayList<>();

        String query = "SELECT e.*,COUNT(s.id) AS session_count FROM event e JOIN participant_event pe ON e.id = pe.event_id LEFT JOIN session s ON e.id=s.event_id WHERE pe.user_id = ? GROUP BY e.id";
        PreparedStatement preparedStatement = connection.prepareStatement(query);
        preparedStatement.setInt(1, user.getId());
        ResultSet resultSet = preparedStatement.executeQuery();
        while (resultSet.next()) {
            Event event = new Event();
            event.setId(resultSet.getInt("id"));
            int userId = resultSet.getInt("orgniser_id");
            User orgniser=userService.getById(userId);
            event.setOrgniser(orgniser);
            event.setTitle(resultSet.getString("title"));
            event.setDescription(resultSet.getString("description"));
            event.setDate_debut(resultSet.getDate("date_debut"));
            event.setDate_fin(resultSet.getDate("date_fin"));
            event.setMax_participants(resultSet.getInt("max_participant"));
            event.setStatus(resultSet.getString("status"));
            event.setImage(resultSet.getString("image"));
            event.setSessionCount(resultSet.getInt("session_count"));
            events.add(event);


        }

        return events;

    }

}
