package pi2425.swappy_javafx.services;

import pi2425.swappy_javafx.entities.Product;
import pi2425.swappy_javafx.utils.MyDatabase;

import java.sql.*;
import java.util.ArrayList;
import java.util.List;

public class ProductService implements IService<Product> {
    private final Connection connection;
    private final UserService userService;
    private final SoukService soukService;

    public ProductService() {
        connection = MyDatabase.getInstance().getConnection();
        userService = new UserService();
        soukService = new SoukService();
    }

    @Override
    public void ajouter(Product product) throws SQLException {
        String query = "INSERT INTO product (owner_id, souk_id, name, description, price, discout_price,image) VALUES (?, ?, ?, ?, ?, ?, ?)";
        try (PreparedStatement statement = connection.prepareStatement(query, Statement.RETURN_GENERATED_KEYS)) {
            statement.setInt(1, product.getOwner_id());
            statement.setInt(2, product.getSouk_id());
            statement.setString(3, product.getProduct_name());
            statement.setString(4, product.getProduct_description());
            statement.setInt(5, product.getProduct_price());
            statement.setInt(6, product.getOld_price());
            statement.setString(7, product.getProduct_image());
            statement.executeUpdate();

            // Set the generated ID
            try (ResultSet generatedKeys = statement.getGeneratedKeys()) {
                if (generatedKeys.next()) {
                    product.setId(generatedKeys.getInt(1));
                }
            }
        }
    }

    @Override
    public void supprimer(Product product) throws SQLException {
        String query = "DELETE FROM product WHERE id = ?";
        try (PreparedStatement statement = connection.prepareStatement(query)) {
            statement.setInt(1, product.getId());
            statement.executeUpdate();
        }
    }

    @Override
    public void modifier(Product product) throws SQLException {
        String query = "UPDATE product SET owner_id = ?, souk_id = ?, name = ?, description = ?, price = ?, discout_price = ?, image = ? WHERE id = ?";
        try (PreparedStatement statement = connection.prepareStatement(query)) {
            statement.setInt(1, product.getOwner_id());
            statement.setInt(2, product.getSouk_id());
            statement.setString(3, product.getProduct_name());
            statement.setString(4, product.getProduct_description());
            statement.setInt(5, product.getProduct_price());
            statement.setInt(6, product.getOld_price());
            statement.setString(7, product.getProduct_image());
            statement.setInt(8, product.getId());
            statement.executeUpdate();
        }
    }

    @Override
    public List<Product> afficher() throws SQLException {
        List<Product> products = new ArrayList<>();
        String query = "SELECT * FROM product";
        try (Statement statement = connection.createStatement();
             ResultSet resultSet = statement.executeQuery(query)) {
            while (resultSet.next()) {
                products.add(createProductFromResultSet(resultSet));
            }
        }
        return products;
    }

    @Override
    public Product getById(int id) throws SQLException {
        String query = "SELECT * FROM product WHERE id = ?";
        try (PreparedStatement statement = connection.prepareStatement(query)) {
            statement.setInt(1, id);
            try (ResultSet resultSet = statement.executeQuery()) {
                if (resultSet.next()) {
                    return createProductFromResultSet(resultSet);
                }
            }
        }
        return null;
    }

    public List<Product> getProductsBySoukId(int soukId) throws SQLException {
        List<Product> products = new ArrayList<>();
        String query = "SELECT * FROM product WHERE souk_id = ?";
        try (PreparedStatement statement = connection.prepareStatement(query)) {
            statement.setInt(1, soukId);
            try (ResultSet resultSet = statement.executeQuery()) {
                while (resultSet.next()) {
                    products.add(createProductFromResultSet(resultSet));
                }
            }
        }
        return products;
    }

    public List<Product> getProductsByOwnerId(int ownerId) throws SQLException {
        List<Product> products = new ArrayList<>();
        String query = "SELECT * FROM product WHERE owner_id = ?";
        try (PreparedStatement statement = connection.prepareStatement(query)) {
            statement.setInt(1, ownerId);
            try (ResultSet resultSet = statement.executeQuery()) {
                while (resultSet.next()) {
                    products.add(createProductFromResultSet(resultSet));
                }
            }
        }
        return products;
    }

    private Product createProductFromResultSet(ResultSet resultSet) throws SQLException {
        Product product = new Product(
                userService.getById(resultSet.getInt("owner_id")).getId(),
                soukService.getById(resultSet.getInt("souk_id")).getSouk_id(),
                resultSet.getString("name"),
                resultSet.getString("description"),
                resultSet.getInt("price")
        );
        product.setId(resultSet.getInt("id"));
        product.setOld_price(resultSet.getInt("discout_price"));
        product.setProduct_image(resultSet.getString("image"));
        return product;
    }
}