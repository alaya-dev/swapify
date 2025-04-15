module pi2425.swappy_javafx {
    requires javafx.fxml;
    requires javafx.web;
    requires javafx.controls;
    requires javafx.graphics;
    requires java.net.http;
    requires java.sql;

    requires okhttp3;
    requires org.json;
    requires pusher.java.client;

    opens pi2425.swappy_javafx.entities to javafx.base;
    exports pi2425.swappy_javafx.entities;
    opens pi2425.swappy_javafx.tests to javafx.fxml;
    exports pi2425.swappy_javafx.tests;
    opens pi2425.swappy_javafx.controllers to javafx.fxml;
    exports pi2425.swappy_javafx.controllers;
    exports pi2425.swappy_javafx.utils;
    opens pi2425.swappy_javafx.utils to javafx.fxml;
}