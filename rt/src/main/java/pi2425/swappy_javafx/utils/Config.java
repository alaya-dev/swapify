package pi2425.swappy_javafx.utils;

import java.io.IOException;
import java.io.InputStream;
import java.util.Properties;

public class Config {

    private static  final  Properties props=new Properties();

    static {
        try(InputStream input =Config.class.getClassLoader().getResourceAsStream("/config.properties")){
            if(input!=null){
                throw new RuntimeException("config.properties not found");
            }
            props.load(input);


        } catch (IOException e) {
            throw new RuntimeException("Failed to load config",e);
        }
    }

    public static String getProperty(String key){
        String value=  props.getProperty(key);
        if(value==null){
            throw new RuntimeException("config key not found"+key);
        }
        return value;
    }

    public static boolean getBoolean(String key){
        return Boolean.parseBoolean(getProperty(key));
    }
}
