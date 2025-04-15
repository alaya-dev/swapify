package pi2425.swappy_javafx.entities;

public class Offre {
    private int id ;
    private int annonce_name_id ;
    private int annonce_owner_id ;
    private int offer_maker_id ;
    private String description ;
    private Etat status ;
    public Offre() {}
    public Offre(int annonce_name_id , int annonce_owner_id , int offer_maker_id , String description , Etat status) {
        this.annonce_name_id = annonce_name_id ;
        this.annonce_owner_id = annonce_owner_id ;
        this.offer_maker_id = offer_maker_id ;
        this.description = description ;
        this.status = Etat.EnAttente ;
    }

    public int getId() {
        return id;
    }

    public void setId(int id) {
        this.id = id;
    }

    public int getAnnonce_name_id() {
        return annonce_name_id;
    }

    public void setAnnonce_name_id(int annonce_name_id) {
        this.annonce_name_id = annonce_name_id;
    }

    public int getAnnonce_owner_id() {
        return annonce_owner_id;
    }

    public void setAnnonce_owner_id(int annonce_owner_id) {
        this.annonce_owner_id = annonce_owner_id;
    }

    public int getOffer_maker_id() {
        return offer_maker_id;
    }

    public void setOffer_maker_id(int offer_maker_id) {
        this.offer_maker_id = offer_maker_id;
    }

    public String getDescription() {
        return description;
    }

    public void setDescription(String description) {
        this.description = description;
    }

    public Etat getStatus() {
        return status;
    }

    public void setStatus(Etat status) {
        this.status = status;
    }
}
