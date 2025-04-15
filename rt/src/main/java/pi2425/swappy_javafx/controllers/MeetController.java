package pi2425.swappy_javafx.controllers;

import javafx.application.Platform;
import javafx.fxml.FXML;
import javafx.scene.control.Alert;
import javafx.scene.web.WebEngine;
import javafx.scene.web.WebView;

import static pi2425.swappy_javafx.utils.NavigationUtils.showAlert;

public class MeetController {

    @FXML
    private WebView webView;

    private String meetingId;
    private boolean isOrganizer;
    private String displayName;
    private String apiKey;


    public void initializeMeeting(String meetingId, boolean isOrganizer, String displayName, String apiKey) {
        WebEngine engine = webView.getEngine();

        // Use String.format() with proper escaping of % signs
        String htmlContent = String.format("""
        <!DOCTYPE html>
        <html>
        <head>
            <script src="https://sdk.videosdk.live/rtc-js-prebuilt/0.3.43/rtc-js-prebuilt.js"></script>
            <style>
                #videosdkContainer {
                    width: 100%%;
                    height: 100%%;
                }
                body { margin: 0; overflow: hidden; }
            </style>
        </head>
        <body>
            <div id="videosdkContainer"></div>
            <script>
                const meeting = new VideoSDKMeeting();
                meeting.init({
                    name: "%s",
                    meetingId: "%s",
                    apiKey: "%s",
                    containerId: "videosdkContainer",
                    micEnabled: true,
                    webcamEnabled: true,
                    participantCanToggleSelfWebcam: true,
                    participantCanToggleSelfMic: true,
                    chatEnabled: true,
                    screenShareEnabled: true,
                    recordingEnabled: %s,
                    participantCanLeave: true,
                    joinScreen: { visible: false },
                    permissions: {
                        endMeeting: %s,
                        toggleParticipantWebcam: %s,
                        toggleParticipantMic: %s,
                        drawOnWhiteboard: true,
                        toggleWhiteboard: %s,
                        changeLayout: true
                    }
                });
            </script>
        </body>
        </html>
        """,
                displayName,
                meetingId,
                apiKey,
                isOrganizer, // recordingEnabled
                isOrganizer, // endMeeting
                isOrganizer, // toggleParticipantWebcam
                isOrganizer, // toggleParticipantMic
                isOrganizer  // toggleWhiteboard
        );

        engine.loadContent(htmlContent);
        engine.setJavaScriptEnabled(true);

        engine.setOnAlert(event -> {
            Platform.runLater(() ->
                    showAlert("Meeting Alert", event.getData(), Alert.AlertType.INFORMATION)
            );
        });
    }
}
