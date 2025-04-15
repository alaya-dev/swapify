package pi2425.swappy_javafx.entities;

public class Product {
    private int id;
    private int owner_id;
    private int souk_id;
    private String product_name;
    private String product_description;
    private int product_price;
    private int old_price;
    private String product_image;

    public Product(int owner_id , int souk_id, String product_name, String product_description, int product_price , int old_price, String product_image) {
        this.owner_id = owner_id;
        this.souk_id = souk_id;
        this.product_name = product_name;
        this.product_description = product_description;
        this.product_price = product_price;
        this.product_image = product_image;
        this.old_price = old_price;
    }

    public Product(int ownerId, int soukId, String productName, String productDescription, int productPrice) {
        this.owner_id = ownerId;
        this.souk_id = soukId;
        this.product_name = productName;
        this.product_description = productDescription;
        this.product_price = productPrice;
    }


    public int getOld_price() {
        return old_price;
    }

    public void setOld_price(int old_price) {
        this.old_price = old_price;
    }

    public int getId() {
        return id;
    }

    public void setId(int id) {
        this.id = id;
    }

    public int getOwner_id() {
        return owner_id;
    }

    public void setOwner_id(int owner_id) {
        this.owner_id = owner_id;
    }

    public int getSouk_id() {
        return souk_id;
    }

    public void setSouk_id(int souk_id) {
        this.souk_id = souk_id;
    }

    public String getProduct_name() {
        return product_name;
    }

    public void setProduct_name(String product_name) {
        this.product_name = product_name;
    }

    public String getProduct_description() {
        return product_description;
    }

    public void setProduct_description(String product_description) {
        this.product_description = product_description;
    }

    public int getProduct_price() {
        return product_price;
    }

    public void setProduct_price(int product_price) {
        this.product_price = product_price;
    }

    public String getProduct_image() {
        return product_image;
    }

    public void setProduct_image(String product_image) {
        this.product_image = product_image;
    }

}
