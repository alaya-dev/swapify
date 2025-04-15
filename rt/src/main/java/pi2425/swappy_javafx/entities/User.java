package pi2425.swappy_javafx.entities;

import java.util.ArrayList;
import java.util.Date;
import java.util.List;

public class User {

    private int id;
    private String nom;
    private String email;
    private String prenom;
    private Date dateNaissance;
    private String adresse;
    private String telephone;
    private boolean isVerified;
    private Date created_at;
    private Date last_connexion;
    private List<String> role;
    private String password;

    // Constructor


    public User(int id, String nom, String email, String prenom, Date dateNaissance, String adresse, String telephone, boolean isVerified, Date created_at, Date last_connexion,  String password) {
        this.id = id;
        this.nom = nom;
        this.email = email;
        this.prenom = prenom;
        this.dateNaissance = dateNaissance;
        this.adresse = adresse;
        this.telephone = telephone;
        this.isVerified = isVerified;
        this.created_at = created_at;
        this.last_connexion = last_connexion;
        this.role = new ArrayList<String>();
        this.password = password;
    }

    public User() {
    }

    public void setId(int id) {
        this.id = id;
    }

    public User(String nom, String email, String prenom, Date dateNaissance, String adresse, String telephone, boolean isVerified, Date created_at, Date last_connexion, String password) {

        this.nom = nom;
        this.email = email;
        this.prenom = prenom;
        this.dateNaissance = dateNaissance;
        this.adresse = adresse;
        this.telephone = telephone;
        this.isVerified = isVerified;
        this.created_at = created_at;
        this.last_connexion = last_connexion;
        this.role = new ArrayList<String>();
        this.password = password;
    }


    public int getId() {
        return id;
    }

    public String getNom() {
        return nom;
    }

    public void setNom(String nom) {
        this.nom = nom;
    }

    public String getEmail() {
        return email;
    }

    public void setEmail(String email) {
        this.email = email;
    }

    public String getPrenom() {
        return prenom;
    }

    public void setPrenom(String prenom) {
        this.prenom = prenom;
    }

    public Date getDateNaissance() {
        return dateNaissance;
    }

    public void setDateNaissance(Date dateNaissance) {
        this.dateNaissance = dateNaissance;
    }

    public String getAdresse() {
        return adresse;
    }

    public void setAdresse(String adresse) {
        this.adresse = adresse;
    }

    public String getTelephone() {
        return telephone;
    }

    public void setTelephone(String telephone) {
        this.telephone = telephone;
    }

    public boolean isVerified() {
        return isVerified;
    }

    public void setVerified(boolean verified) {
        isVerified = verified;
    }

    public Date getCreated_at() {
        return created_at;
    }

    public void setCreated_at(Date created_at) {
        this.created_at = created_at;
    }

    public Date getLast_connexion() {
        return last_connexion;
    }

    public void setLast_connexion(Date last_connexion) {
        this.last_connexion = last_connexion;
    }

    public List<String> getRole() {
        return role;
    }

    public void setRole(List<String> role) {
        this.role = role;
    }

    public String getPassword() {
        return password;
    }

    public void setPassword(String password) {
        this.password = password;
    }

    @Override
    public String toString() {
        return "User{" +
                "id=" + id +
                ", nom='" + nom + '\'' +
                ", email='" + email + '\'' +
                ", prenom='" + prenom + '\'' +
                ", dateNaissance=" + dateNaissance +
                ", adresse='" + adresse + '\'' +
                ", telephone='" + telephone + '\'' +
                ", isVerified=" + isVerified +
                ", created_at=" + created_at +
                ", last_connexion=" + last_connexion +
                ", role=" + role +
                ", password='" + password + '\'' +
                '}';
    }


}
