package jet;


import java.io.File;
import java.io.FileOutputStream;
import java.io.IOException;
import java.io.InputStream;
import java.net.HttpURLConnection;
import java.net.URL;
import java.util.Locale;
import java.util.regex.Matcher;
import java.util.regex.Pattern;

/**
 * Ein Helfer um Daten aus Webinterfaces abzusaugen
 * @author marcel
 */
public class WebGetHelper {
    
    protected String url;
    protected Pattern regex;
    protected Matcher matcher = null;
    
    protected String userAgent = "CallOfMesk";

    public WebGetHelper(String url, String regex) {
        this.url = url;
        this.regex = Pattern.compile(regex);
    }
    
    public WebGetHelper(String url, String regex, String userAgent) {
        this.url = url;
        this.regex = Pattern.compile(regex);
        this.userAgent = userAgent;
    }

    /**
     * Daten abrufen (via http)
     * @param parameters Parameter die in die URL eingesetzt werden sollen
     * @return true, wenn alles gut geht und ein Ergebnis entdeckt wurde
     */
    public boolean getStuff(Object... parameters){
//        if (Boolean.parseBoolean(GameObjectManager.getInstance().getProperty("proxy"))) {
//            System.setProperty("http.proxyHost", GameObjectManager.getInstance().getProperty("proxyhost"));
//            System.setProperty("http.proxyPort", GameObjectManager.getInstance().getProperty("proxyport"));
//        }
        String request = String.format(Locale.US, url, parameters);
        System.out.println("request = " + request);
        
//        String data = "blsbls <span> daten </span> <span> nochwas </span> bla";

        StringBuilder sb = new StringBuilder();
        try {
            HttpURLConnection httpURLConnection =
                    (HttpURLConnection) new URL(request).openConnection();
            httpURLConnection.addRequestProperty("Accept", "*/*");
            httpURLConnection.addRequestProperty("User-Agent", userAgent);
            httpURLConnection.connect();
            InputStream is = httpURLConnection.getInputStream();
            byte[] buffer = new byte[1024];
            int n;
            while ((n = is.read(buffer)) > 0) {
                sb.append(new String(buffer, 0, n));
//                System.out.println("n = " + n);
            }
            is.close();
            httpURLConnection.disconnect();
            
            FileOutputStream fos = new FileOutputStream(new File("temp.out"));
            fos.write(sb.toString().getBytes());
            fos.close();
            
        } catch (IOException iOException) {
            iOException.printStackTrace();
            return false;
        } 

        matcher = regex.matcher(sb);
        return matcher.find();
    }

    /**
     * Gibt das gefundene Ergbnis das in der RE in der index-ten Klammer war zurueck. 
     * @param index
     * @return 
     */
    public String getResult(int index) {
        return matcher.group(index);
    }

    /**
     * Versucht noch ein passendes ergebnis zu finden.
     * @return true, wenn noch ein Match gefunden wurde.
     */
    public boolean findNext() {
        return matcher.find();
    }

}
