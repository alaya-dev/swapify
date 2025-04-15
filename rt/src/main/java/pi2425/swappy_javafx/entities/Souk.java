package pi2425.swappy_javafx.entities;

import java.time.Instant;

public class Souk {
    private int souk_id;
    private String souk_name;
    private Instant souk_start;
    private Instant souk_end;

    public Souk(String souk_name, Instant souk_start, Instant souk_end) {
        this.souk_name = souk_name;
        this.souk_start = souk_start;
        this.souk_end = souk_end;
    }



    @Override
    public String toString() {
        return "Souk{" +
                "souk_id=" + souk_id +
                ", souk_name='" + souk_name + '\'' +
                ", souk_start=" + souk_start +
                ", souk_end=" + souk_end +
                '}';
    }

    public int getSouk_id() {
        return souk_id;
    }

    public void setSouk_id(int souk_id) {
        this.souk_id = souk_id;
    }

    public String getSouk_name() {
        return souk_name;
    }

    public void setSouk_name(String souk_name) {
        this.souk_name = souk_name;
    }

    public Instant getSouk_start() {
        return souk_start;
    }

    public void setSouk_start(Instant souk_start) {
        this.souk_start = souk_start;
    }

    public Instant getSouk_end() {
        return souk_end;
    }

    public void setSouk_end(Instant souk_end) {
        this.souk_end = souk_end;
    }
}
