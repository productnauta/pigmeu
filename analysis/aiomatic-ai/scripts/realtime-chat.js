"use strict";
let pc = null;
let dc = null;
let mediaStream = null;
let audioEl = null;
let isRecording = false;
let recognition = null; 
let recognizing = false;

function appendToLog(role, text, chatLogEl) 
{
  if(text == '')
  {
    return;
  }
  if (!chatLogEl)
  {
    return;
  }
  if(role == 'user')
  {
    const messageEl = document.createElement("div");
    messageEl.style.marginBottom = "10px";
    messageEl.className = "ai-bubble ai-speech";
    messageEl.innerHTML = text;
    const lastTranscript = Array.from(chatLogEl.children)
    .filter(div => div.classList.contains("ai-bubble") && 
                   div.classList.contains("ai-transcript") && 
                   !div.hasAttribute("data-message-included"))
    .pop();
    if (lastTranscript) {
      chatLogEl.insertBefore(messageEl, lastTranscript);
      lastTranscript.setAttribute("data-message-included", "true");
    }
    else
    {
      chatLogEl.appendChild(messageEl);
    }
  }
  else
  {
    const messageEl = document.createElement("div");
    messageEl.style.marginBottom = "10px";
    messageEl.innerHTML = `<div class="ai-bubble ai-transcript">${text}</div>`;
    chatLogEl.appendChild(messageEl);
  }
  chatLogEl.scrollTop = chatLogEl.scrollHeight;
}
async function appendStreamToLog(responseId, text, container) 
{
  if(text == '')
  {
    return;
  }
  if (!container) 
  {
    return;
  }
  if(responseId == 'user')
  {
    let transcriptDiv = document.createElement("div");
    transcriptDiv.className = "ai-bubble ai-speech";
    transcriptDiv.textContent = text;
    const lastTranscript = Array.from(container.children)
    .filter(div => div.classList.contains("ai-bubble") && 
                   div.classList.contains("ai-transcript") && 
                   !div.hasAttribute("data-message-included"))
    .pop();
    if (lastTranscript) {
      container.insertBefore(transcriptDiv, lastTranscript);
      lastTranscript.setAttribute("data-message-included", "true");
    }
    else
    {
      container.appendChild(transcriptDiv);
    }
  }
  else
  {
    let transcriptDiv = document.querySelector(`[data-response-id="${responseId}"]`);
    if (!transcriptDiv) 
    {
        transcriptDiv = document.createElement("div");
        transcriptDiv.className = "ai-bubble ai-transcript";
        transcriptDiv.setAttribute("data-response-id", responseId);
        container.appendChild(transcriptDiv);
    }
    transcriptDiv.textContent = transcriptDiv.textContent + text;
  }
}
async function startSession() {
  try {
    var enable_god_mode = aiomatic_realtime.enable_god_mode;
    if(enable_god_mode == '')
    {
        enable_god_mode = 'off';
    }
    const tokenResponse = await fetch(aiomatic_realtime.ajax_url, {
      method: "POST",
      body: new URLSearchParams({
        action: "aiomatic_realtime_session",
        nonce: aiomatic_realtime.nonce,
        model: aiomatic_realtime.model,
        voice: aiomatic_realtime.voice,
        instructions: aiomatic_realtime.instructions,
        temperature: aiomatic_realtime.temperature,
        max_tokens: aiomatic_realtime.max_tokens,
        enable_god_mode: enable_god_mode,
        user_id: aiomatic_realtime.user_id,
        user_token_cap_per_day: aiomatic_realtime.user_token_cap_per_day,
      }),
    });

    const data = await tokenResponse.json();
    if (!data.success) {
      alert(data.data.message);
      return;
    }

    const EPHEMERAL_KEY = data.data.client_secret;

    pc = new RTCPeerConnection();

    audioEl = document.createElement("audio");
    audioEl.autoplay = true;
    pc.ontrack = (e) => {
      audioEl.srcObject = e.streams[0];
      document.body.appendChild(audioEl); 
    };

    mediaStream = await navigator.mediaDevices.getUserMedia({ audio: true });
    pc.addTrack(mediaStream.getTracks()[0]);

    dc = pc.createDataChannel("oai-events");
    dc.addEventListener("open", () => {
      console.log("Data channel open");
    });
    let chatLogEl = document.getElementById("aiomatic-realtime-log");


    dc.addEventListener("message", async (e) => {
      const event = JSON.parse(e.data);
      console.log("Received event:", event);
      if (event.type && event.type === "response.done") {
        if (event.response && event.response.usage) {
            logAiUsage(event.response, aiomatic_realtime.model);
        }
        const functionCall = event.response.output.find((item) => item.type === "function_call");
        if (functionCall) 
        {
            const { name, arguments: args, call_id } = functionCall;
            console.log('Calling function ' + name);
            jQuery.ajax({
                type: 'POST',
                async: false,
                url: aiomatic_realtime.ajax_url,
                data: {
                    action: 'aiomatic_call_ai_function_realtime',
                    nonce: aiomatic_realtime.persistentnonce,
                    name: name,
                    args: args,
                    call_id: call_id
                },
                success: function(result) 
                {
                    if (typeof result !== "object" || result === null) {
                        try {
                            result = JSON.parse(result);
                        } catch (error) {
                            console.error("Failed to parse function JSON:", error);
                        }
                    }
                    if(result.scope == 'fail')
                    {
                        console.log('Error while calling parsing functions: ' + JSON.stringify(result));
                        sendFunctionResult(call_id, { response: JSON.stringify({ status: 'error', message: 'Error while calling parsing functions: ' + JSON.stringify(result) }) });
                    }
                    else
                    {
                        if(result.scope == 'response')
                        {
                            console.log('Sending function response to the AI: ' + result.data);
                            sendFunctionResult(call_id, { response: JSON.stringify({ status: 'success', message: result.data }) });
                        }
                        else
                        {
                            if(result.scope == 'user_message')
                            {
                                console.log('Displaying function data to the user.');
                                sendFunctionResult(call_id, { response: JSON.stringify({ status: 'success', message: 'Data displayed to user.' }) });
                                appendToLog("assistant", result.data, chatLogEl);
                            }
                            else
                            {
                                console.log('Unknown scope returned: ' + JSON.stringify(result));
                                sendFunctionResult(call_id, { response: 'Error: Unknown scope returned: ' + JSON.stringify(result) });
                            }
                        }
                    }
                },
                error: function(error) {
                    console.log('Error while calling AI functions: ' + error.responseText);
                    sendFunctionResult(call_id, { response: JSON.stringify({ status: 'error', message: 'Error while calling AI functions: ' + error.responseText }) });
                },
            });
        }
      }
    
      if (event.type === "response.done") {
        console.log("Response completed.");
      }
      if (event.type === "response.audio_transcript.delta") {
        if(aiomatic_realtime.show_chat_log == 'show')
        {
          const responseId = event.response_id;
          const text = event.delta || "";
          appendStreamToLog(responseId, text, chatLogEl);
        }
      }
    });

    if(aiomatic_realtime.show_user_log == 'show')
    {
      if ("webkitSpeechRecognition" in window) {
        recognition = new webkitSpeechRecognition();
        recognition.continuous = true;
        recognition.interimResults = true;

        recognition.onresult = function (event) {
          for (let i = event.resultIndex; i < event.results.length; ++i) {
            if (event.results[i].isFinal) {
              const text = event.results[i][0].transcript.trim();
              appendToLog("user", text, chatLogEl);
            }
          }
        };

        recognition.onerror = function (event) {
          console.error("Speech recognition error:", event);
        };

        recognition.onend = function () {
          if (isRecording) recognition.start();
        };

        recognition.start();
        recognizing = true;
      } else {
        console.log("Web Speech API is not supported in this browser.");
      }
    }

    const offer = await pc.createOffer();
    await pc.setLocalDescription(offer);

    const sdpResponse = await fetch(
      `https://api.openai.com/v1/realtime?model=${aiomatic_realtime.model}`,
      {
        method: "POST",
        body: offer.sdp,
        headers: {
          Authorization: `Bearer ${EPHEMERAL_KEY.value}`,
          "Content-Type": "application/sdp",
        },
      }
    );

    const answer = {
      type: "answer",
      sdp: await sdpResponse.text(),
    };
    await pc.setRemoteDescription(answer);

    isRecording = true;
    updateUI(true);

    console.log("Session started.");
  } catch (err) {
    console.error("Error starting session:", err);
    updateUI(false);
  }
}

function stopSession() {
  if (pc) {
    pc.close();
    pc = null;
  }

  if (mediaStream) {
    mediaStream.getTracks().forEach((track) => track.stop());
    mediaStream = null;
  }

  if (audioEl) {
    audioEl.remove();
    audioEl = null;
  }

  if (recognition) {
    recognition.stop();
    recognition = null;
    recognizing = false;
  }

  isRecording = false;
  updateUI(false);

  console.log("Session stopped.");
}

function updateUI(recording) {
  const statusEl = document.getElementById("aiomatic-realtime-status");
  const toggleBtn = document.getElementById("aiomatic-realtime-toggle");
  if (!statusEl || !toggleBtn) return;

  if (recording) {
    statusEl.textContent = aiomatic_realtime.recording;
    toggleBtn.textContent = aiomatic_realtime.stop_recording;
  } else {
    statusEl.textContent = aiomatic_realtime.not_recording;
    toggleBtn.textContent = aiomatic_realtime.start_recording;
  }
}

document.addEventListener("DOMContentLoaded", () => {
  const toggleBtn = document.getElementById("aiomatic-realtime-toggle");
  if (!toggleBtn) return;

  if (aiomatic_realtime.autostart == 'yes' || parseInt(aiomatic_realtime.autostart, 10) === 1) {
    startSession();
  }

  toggleBtn.addEventListener("click", async () => {
    toggleBtn.disabled = true;
    toggleBtn.classList.add("btn-processing");
  
    try {
      if (!isRecording) {
        await startSession();
      } else {
        stopSession();
      }
    } catch (err) {
      console.error(err);
    }
  
    toggleBtn.disabled = false;
    toggleBtn.classList.remove("btn-processing");
  });
});

function sendFunctionResult(callId, output) {
  dc.send(
    JSON.stringify({
      type: "conversation.item.create",
      item: {
        type: "function_call_output",
        call_id: callId,
        output: JSON.stringify(output),
      },
    })
  );
  dc.send(JSON.stringify({ type: "response.create" }));
}
function logAiUsage(usageData, model) 
{
  jQuery.ajax({
      type: "POST",
      url: aiomatic_realtime.ajax_url,
      data: {
        action: "aiomatic_log_realtime_usage",     
        model: model,
        user_id: aiomatic_realtime.user_id,
        user_token_cap_per_day: aiomatic_realtime.user_token_cap_per_day, 
        nonce: aiomatic_realtime.nonce,      
        usage_data: JSON.stringify(usageData) 
      },
      success: function (response) {
        if(typeof response === 'string' || response instanceof String)
        {
            try {
                var responset = JSON.parse(response);
                response = responset;
            } catch (error) {
                console.error("An error occurred while parsing the JSON: " + error + ' Json: ' + response);
            }
        }
        if(response.status == 'usage')
        {
          alert(response.msg);
          stopSession();
        }
      },
      error: function (err) {
        console.error("Error logging usage:", err);
      },
  });
}