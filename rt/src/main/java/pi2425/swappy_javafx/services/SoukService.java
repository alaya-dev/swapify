package pi2425.swappy_javafx.services;

import pi2425.swappy_javafx.entities.Souk;
import pi2425.swappy_javafx.utils.MyDatabase;

import java.sql.*;
import java.util.ArrayList;
import java.util.List;

public class SoukService implements IService<Souk> {

     Connection cnx;
    public SoukService() {
        cnx = MyDatabase.getInstance().getConnection();
    }

    @Override
    public void ajouter(Souk souk) throws SQLException {
        String req = "INSERT INTO souk (souk_name, souk_start, souk_end) VALUES (?, ?, ?)";
        try (PreparedStatement pst = cnx.prepareStatement(req)) {
            pst.setString(1, souk.getSouk_name());
            pst.setTimestamp(2, Timestamp.from(souk.getSouk_start()));
            pst.setTimestamp(3, Timestamp.from(souk.getSouk_end()));
            pst.executeUpdate();
        }
    }

    @Override
    public void supprimer(Souk souk) throws SQLException {
        String req = "DELETE FROM souk WHERE souk_id = ?";
        try (PreparedStatement pst = cnx.prepareStatement(req)) {
            pst.setInt(1, souk.getSouk_id());
            pst.executeUpdate();
        }
    }

    @Override
    public void modifier(Souk souk) throws SQLException {
        String req = "UPDATE souk SET souk_name = ?, souk_start = ?, souk_end = ? WHERE souk_id = ?";
        try (PreparedStatement pst = cnx.prepareStatement(req)) {
            pst.setString(1, souk.getSouk_name());
            pst.setTimestamp(2, Timestamp.from(souk.getSouk_start()));
            pst.setTimestamp(3, Timestamp.from(souk.getSouk_end()));
            pst.setInt(4, souk.getSouk_id());
            pst.executeUpdate();
        }
    }

    @Override
    public List<Souk> afficher() throws SQLException {
        List<Souk> list = new ArrayList<>();
        String req = "SELECT * FROM souk";
        try (Statement st = cnx.createStatement();
             ResultSet rs = st.executeQuery(req)) {
            while (rs.next()) {
                Souk souk = new Souk(
                        rs.getString("name"),
                        rs.getTimestamp("start_souke").toInstant(),
                        rs.getTimestamp("end_souke").toInstant()
                );
                souk.setSouk_id(rs.getInt("id"));
                list.add(souk);
            }
        }
        return list;
    }

    @Override
    public Souk getById(int id) throws SQLException {
        String req = "SELECT * FROM souk WHERE id = ?";
        try (PreparedStatement pst = cnx.prepareStatement(req)) {
            pst.setInt(1, id);
            try (ResultSet rs = pst.executeQuery()) {
                if (rs.next()) {
                    Souk souk = new Souk(
                            rs.getString("name"),
                            rs.getTimestamp("start_souke").toInstant(),
                            rs.getTimestamp("end_souke").toInstant()
                    );
                    souk.setSouk_id(rs.getInt("id"));
                    return souk;
                }
            }
        }
        return null;
    }

    public void joinSouk(int userId, int soukId) throws SQLException {
        String req = "INSERT INTO souk_user (user_id, souk_id) VALUES (?, ?)";
        try (PreparedStatement pst = cnx.prepareStatement(req)) {
            pst.setInt(1, userId);
            pst.setInt(2, soukId);
            pst.executeUpdate();
        }
    }

    public List<Souk> getAvailableSouks(int userId) throws SQLException {
        List<Souk> list = new ArrayList<>();
        String req = "SELECT s.* FROM souk s WHERE s.souk_id NOT IN " +
                "(SELECT sp.souk_id FROM souk_user sp WHERE sp.user_id = ?) " +
                "AND s.souk_end > CURRENT_TIMESTAMP";
        try (PreparedStatement pst = cnx.prepareStatement(req)) {
            pst.setInt(1, userId);
            try (ResultSet rs = pst.executeQuery()) {
                while (rs.next()) {
                    Souk souk = new Souk(
                            rs.getString("souk_name"),
                            rs.getTimestamp("souk_start").toInstant(),
                            rs.getTimestamp("souk_end").toInstant()
                    );
                    souk.setSouk_id(rs.getInt("souk_id"));
                    list.add(souk);
                }
            }
        }
        return list;
    }

    public List<Souk> getUserSouks(int userId) throws SQLException {
        List<Souk> list = new ArrayList<>();
        String req = "SELECT s.* FROM souk s JOIN souk_user sp ON s.souk_id = sp.souk_id " +
                "WHERE sp.user_id = ?";
        try (PreparedStatement pst = cnx.prepareStatement(req)) {
            pst.setInt(1, userId);
            try (ResultSet rs = pst.executeQuery()) {
                while (rs.next()) {
                    Souk souk = new Souk(
                            rs.getString("souk_name"),
                            rs.getTimestamp("souk_start").toInstant(),
                            rs.getTimestamp("souk_end").toInstant()
                    );
                    souk.setSouk_id(rs.getInt("souk_id"));
                    list.add(souk);
                }
            }
        }
        return list;
    }

    public boolean isUserParticipant(int userId, int soukId) throws SQLException {
        String query = "SELECT COUNT(*) FROM souk_user WHERE user_id = ? AND souk_id = ?";
        try (PreparedStatement statement = cnx.prepareStatement(query)) {
            statement.setInt(1, userId);
            statement.setInt(2, soukId);
            ResultSet resultSet = statement.executeQuery();
            if (resultSet.next()) {
                return resultSet.getInt(1) > 0;
            }
        }
        return false;
    }

}
