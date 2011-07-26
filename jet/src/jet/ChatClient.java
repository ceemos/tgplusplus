package jet;

import java.io.UnsupportedEncodingException;
import java.net.URLEncoder;
import java.security.Key;
import java.util.HashMap;

/**
 *
 * @author marcel
 */
public class ChatClient {

    String server = "http://localhost/~marcel/duesenchat/duesenchat.php?user=%s&action=%s&msg=%s";
    WebGetHelper wgh = new WebGetHelper(server, "([^\n]+)\n(.+)");
    
    Key privatersa;
    Key pubkeyserver;
    
    String localuser = "jet-user";
    String remoteuser = "somebody";
    
    HashMap<Integer, String> symkeys = new HashMap<Integer, String>();

    public void setLocaluser(String localuser) {
        this.localuser = localuser;
    }

    public void setRemoteuser(String remoteuser) {
        this.remoteuser = remoteuser;
    }

    public String getLocaluser() {
        return localuser;
    }

    public String getRemoteuser() {
        return remoteuser;
    }

    public void sendMsg(String msg){
        wgh.getStuff(remoteuser, "send", urlencode(formatmsg(msg, "plain")));
    }
    
    boolean errorlasttime = false;
    
    public String pollMsg(){
        if(errorlasttime){ // damit der Server nicht überschwemmt wird, wenn der daemon abstürtzt
            errorlasttime = false;
            try {
                int t = (int) (3000 * (Math.random() + 1));
                System.out.println("Error last time, waiting " + t + "ms");
                Thread.sleep(t);
            } catch (InterruptedException interruptedException) {
            }
        }
        if(wgh.getStuff(localuser, "poll", "")){
            String head = wgh.getResult(1);
            String msg = wgh.getResult(2);
            wgh.findNext();
            if("plain".equals(head)){
                return msg;
            } else {
                return "Cant understand crypto.";
            }
        } else {
            errorlasttime = true;
        }
        // Timeout, exception o. Ä.
        return null;
    }
    
    private String formatmsg(String msg, String head){
        return head + "\n" + msg;
    }
    
    private String urlencode(String s){
        try {
            return URLEncoder.encode(s, "utf-8");
        } catch (UnsupportedEncodingException unsupportedEncodingException) {
            System.out.println("Unsupportet encoding. Sending unencoded.");
        }
        return s;
    }
    
    
}
