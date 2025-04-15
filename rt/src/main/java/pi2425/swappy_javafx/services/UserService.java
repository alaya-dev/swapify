package pi2425.swappy_javafx.services;

import pi2425.swappy_javafx.entities.Event;
import pi2425.swappy_javafx.entities.User;
import pi2425.swappy_javafx.utils.MyDatabase;

import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.List;

public class UserService implements IService<User> {

    Connection connection;
    public UserService() {
        connection= MyDatabase.getInstance().getConnection();

    }
    @Override
    public void ajouter(User user) throws SQLException {

    }

    @Override
    public void supprimer(User user) throws SQLException {

    }

    @Override
    public void modifier(User user) throws SQLException {

    }

    @Override
    public List<User> afficher() throws SQLException {
        return List.of();
    }

    @Override
    public User getById(int id) throws SQLException {
        String sql = "SELECT id,nom, prenom, email, date_naissance, adresse, tel FROM user WHERE id = ?";

        PreparedStatement preparedStatement = connection.prepareStatement(sql);

        preparedStatement.setInt(1, id);
        ResultSet resultSet = preparedStatement.executeQuery();
        if (resultSet.next()) {

            User user = new User();
            user.setId(resultSet.getInt("id"));
            user.setNom(resultSet.getString("nom"));
            user.setPrenom(resultSet.getString("prenom"));
            user.setEmail(resultSet.getString("email"));
            user.setDateNaissance(resultSet.getDate("date_naissance"));
            user.setAdresse(resultSet.getString("adresse"));
            user.setTelephone(resultSet.getString("tel"));
            return user;
        }
        return null;



    }
}
