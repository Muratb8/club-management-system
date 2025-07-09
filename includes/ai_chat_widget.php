<style>
#ai-chat-widget-container {
    position: fixed;
    right: 28px;
    bottom: 28px;
    z-index: 9999;
    font-family: 'Poppins', Arial, sans-serif;
}
#ai-chat-toggle-btn {
    background: #2E49A5;
    color: #fff;
    border: none;
    border-radius: 12px;
    width: 54px;
    height: 54px;
    box-shadow: 0 2px 8px #0001;
    font-size: 26px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.2s;
}
#ai-chat-toggle-btn:hover {
    background: #1d3577;
}
#ai-chat-box {
    display: none;
    width: 340px;
    height: 420px;
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 4px 16px #2E49A522;
    flex-direction: column;
    overflow: hidden;
    margin-bottom: 8px;
    border: 2px solid #2E49A5;
}
#ai-chat-header {
    background: #2E49A5;
    color: #fff;
    padding: 12px 16px;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: space-between;
    letter-spacing: 0.5px;
}
#ai-chat-close {
    background: none;
    border: none;
    color: #fff;
    font-size: 22px;
    cursor: pointer;
    transition: color 0.2s;
}
#ai-chat-close:hover {
    color: #ffc107;
}
#ai-chat-messages {
    flex: 1;
    padding: 16px;
    overflow-y: auto;
    background: #f4f7fa;
    font-size: 15px;
}
#ai-chat-form {
    display: flex;
    border-top: 1px solid #eee;
    background: #fff;
}
#ai-chat-input {
    flex: 1;
    border: none;
    padding: 12px;
    font-size: 15px;
    outline: none;
    background: #fff;
}
#ai-chat-send-btn {
    background: #2E49A5;
    color: #fff;
    border: none;
    padding: 0 18px;
    font-size: 18px;
    cursor: pointer;
    border-radius: 0 0 16px 0;
    transition: background 0.2s;
}
#ai-chat-send-btn:hover {
    background: #1d3577;
}
.ai-chat-message-user {
    text-align: right;
    margin-bottom: 8px;
    color: #fff;
    background: #2E49A5;
    display: inline-block;
    padding: 8px 14px;
    border-radius: 16px 16px 4px 16px;
    max-width: 80%;
    word-break: break-word;
}
.ai-chat-message-ai {
    text-align: left;
    margin-bottom: 8px;
    color: #222;
    background: #f0f0f0;
    display: inline-block;
    padding: 8px 14px;
    border-radius: 16px 16px 16px 4px;
    max-width: 80%;
    word-break: break-word;
}
</style>
<div id="ai-chat-widget-container">
    <div id="ai-chat-box">
        <div id="ai-chat-header">
            <span><i class="bi bi-robot"></i> Yapay Zeka Asistanı</span>
            <button id="ai-chat-close" title="Kapat">&times;</button>
        </div>
        <div id="ai-chat-messages"></div>
        <form id="ai-chat-form" autocomplete="off">
            <input type="text" id="ai-chat-input" placeholder="Sorunuzu yazın..." />
            <button type="submit" id="ai-chat-send-btn">&#9658;</button>
        </form>
    </div>
    <button id="ai-chat-toggle-btn" title="Yapay Zeka Asistanı"><i class="bi bi-robot"></i></button>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const toggleBtn = document.getElementById('ai-chat-toggle-btn');
    const chatBox = document.getElementById('ai-chat-box');
    const closeBtn = document.getElementById('ai-chat-close');
    const form = document.getElementById('ai-chat-form');
    const input = document.getElementById('ai-chat-input');
    const messages = document.getElementById('ai-chat-messages');

    toggleBtn.onclick = () => { chatBox.style.display = 'flex'; toggleBtn.style.display = 'none'; };
    closeBtn.onclick = () => { chatBox.style.display = 'none'; toggleBtn.style.display = 'flex'; };

    form.onsubmit = function (e) {
        e.preventDefault();
        const userMsg = input.value.trim();
        if (!userMsg) return;
        messages.innerHTML += `<div class="ai-chat-message-user"><b>Sen:</b> ${userMsg}</div>`;
        input.value = '';
        messages.scrollTop = messages.scrollHeight;
        fetch('../backend/ai_api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ message: userMsg })
        })
        .then(res => res.json())
        .then(data => {
            let reply = data.reply;
            // 300 karakterden fazlasını kes, sonuna ... ekle
            if (reply.length > 300) {
                reply = reply.substring(0, 297) + '...';
            }
            messages.innerHTML += `<div class="ai-chat-message-ai"><b>Asistan:</b> ${reply}</div>`;
            messages.scrollTop = messages.scrollHeight;
        })
        .catch(() => {
            messages.innerHTML += `<div style="color:red;">Bir hata oluştu.</div>`;
        });
    };
});
</script>