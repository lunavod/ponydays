function getCookie(name) {
    var matches = document.cookie.match(new RegExp(
        "(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
    ));
    return matches ? decodeURIComponent(matches[1]) : undefined;
}

const io = require("socket.io-client")

window.sock = io(SOCKET_URL, {
    query: {
        token: getCookie("key")
    }
})

sock.on('reconnect_attempt', () => {
    sock.io.opts.query = {
        token: getCookie("key")
    }
});

function checkPerm(key) {
    let perm = localStorage.getItem(key)
    return (perm == null || parseInt(perm))
}

window.nAudio = new Audio()
nAudio.src = localStorage.getItem("notice_sound_url") || "http://freesound.org/data/previews/245/245645_1038806-lq.mp3"

sock.on("reply-info", function (data) {
    console.log("DATA:", data)
    if (checkPerm("notice_reply")) {
        let url = "/blog/undefined/" + data.targetId + "#comment" + data.commentData.id
        let blank = true
        if (location.pathname.startsWith("/blog/") && location.pathname.endsWith(data.targetId)) {
            url = "#comment" + data.commentData.id
            blank = false
        }
        ls.msg.notice(data.senderLogin + " ответил вам в посте " + data.targetTitle, data.commentData.text, url, blank)
        if (checkPerm("sound_notice"))
            nAudio.play()
    }
})

sock.on("edit-comment-info", function (data) {
    console.log("DATA:", data)
    if (checkPerm("notice_comment_delete")) {
        let url = "/blog/undefined/" + data.targetId + "#comment" + data.commentData.id
        let blank = true
        if (location.pathname.startsWith("/blog/") && location.pathname.endsWith(data.targetId)) {
            url = "#comment" + data.commentData.id
            blank = false
        }
        ls.msg.notice(data.senderLogin + " отредактировал ваш комментарий", data.commentData.text, url, blank)
        if (checkPerm("sound_notice"))
            nAudio.play()
    }
})

sock.on("delete-comment-info", function (data) {
    console.log("DATA:", data)
    data.delete = parseInt(data.delete)
    if (checkPerm("notice_comment_delete")) {
        let url = "/blog/undefined/" + data.targetId + "#comment" + data.commentId
        let blank = true
        if (location.pathname.startsWith("/blog/") && location.pathname.endsWith(data.targetId)) {
            url = "#comment" + data.commentId
            blank = false
        }
        if (data.delete) {
            ls.msg.notice(data.senderLogin + " удалил ваш комментарий", data.deleteReason, url, blank)
        } else {
            ls.msg.notice(data.senderLogin + " восстановил ваш комментарий", data.commentText, url, blank)
        }
        if (checkPerm("sound_notice"))
            nAudio.play()
    }
})

sock.on("talk-answer", function (data) {
    console.log("DATA:", data)
    if (checkPerm("notice_talk_reply")) {
        let url = "/talk/read/" + data.targetId + "#comment" + data.commentData.id
        let blank = true
        if (location.pathname.startsWith("/talk/") && (location.pathname.endsWith(data.targetId) || location.pathname.endsWith(data.targetId + "/"))) {
            url = "#comment" + data.commentData.id
            blank = false
        }
        ls.msg.notice("Новый комментарий в " + data.targetTitle + ", от " + data.senderLogin, data.commentText, url, blank)
        if (checkPerm("sound_notice"))
            nAudio.play()
    }
})

sock.on("vote-info", function (data) {
    console.log("DATA:", data)
    if (checkPerm("notice_vote")) {
        let url = "/blog/undefined/" + data.targetId
        let blank = true
        if (data.targetParentId) {
            url = "/blog/undefined/" + data.targetParentId + "#comment" + data.targetId
        }
        if (location.pathname.startsWith("/blog/") && (location.pathname.endsWith(data.targetParentId) || location.pathname.endsWith(data.targetParentId + "/"))) {
            url = "#comment" + data.targetId
            blank = false
        }
        if (location.pathname.startsWith("/blog/") && (location.pathname.endsWith(data.targetId) || location.pathname.endsWith(data.targetId + "/")) && data.targetType == "topic") {
            url = "#"
            blank = false
        }
        ls.msg.notice("За ваш " + (data.targetType == "comment" ? "комментарий" : "пост") + " проголосовали", data.commentText || data.topicTitle, url, blank)
        if (checkPerm("sound_notice"))
            nAudio.play()
    }
})

sock.on('edit-comment', (data) => ls.emitter.emit("socket-edit-comment", data))
sock.on('delete-comment', (data) => ls.emitter.emit("socket-delete-comment", data))
sock.on('new-comment', (data) => {
    console.log("DATA:", data.commentData);
    ls.emitter.emit("socket-new-comment", data);
})

sock.on('new-vote', function (data) {
    console.log("VOTE!", data)
    let area = data.targetType == "comment" ? $("#vote_area_" + data.targetType + "_" + data.targetId) : $("#vote_total_" + data.targetType + "_" + data.targetId)
    if (data.rating == 0) {
        area.removeClass("vote-count-positive")
        area.removeClass("vote-count-negative")
        $("#vote_total_" + data.targetType + "_" + data.targetId).html(data.rating)
    } else if (data.rating > 0) {
        area.addClass("vote-count-positive")
        area.removeClass("vote-count-negative")
        area.removeClass("action-hidden")
        $("#vote_total_" + data.targetType + "_" + data.targetId).html("+" + data.rating)
    } else {
        area.addClass("vote-count-negative")
        area.removeClass("vote-count-positive")
        area.removeClass("action-hidden")
        $("#vote_total_" + data.targetType + "_" + data.targetId).html(data.rating)
    }
})

sock.on('site-update', function () {
    ls.msg.notice('Сайт был обновлен', 'Рекомендуется перезагрузить страницу')
    if (checkPerm("sound_notice"))
        nAudio.play()
})

function changeRTCstatus(status) {
    $("#webrtc-status")[0].className = status
}

function updateRTCusers(users) {
    rtcUsers = users
    $("#webrtc-active").html(rtcUsers.map((s)=>`<img src="${s}" class="avatar" width=20 height=20 />`))
}

if (LOGGED_IN) {
    const SimpleWebRTC = require("simplewebrtc")
    $(document.body).append(`<div id="webrtc">
        <div id="webrtc-header">
            <div id="webrtc-active"><span id="webrtc-no-active">Нет активных подключений</span></div>
            <div id="webrtc-actions">
                <span id="webrtc-status" class="inactive"></span>
                <a href="#" id="webrtc-join"><i class="material-icons">person_add</i></a>
                <a href="#" id="webrtc-leave" style="display: none;"><i class="material-icons">exit_to_app</i></a>
            </div>
        </div> 
    </div>`)
    $("#webrtc-join").click(function(){
        function cb () {
            wrtc.joinRoom('your awesome room name')
            sock.emit("joinRTC")

            $("#webrtc-join").css("display","none")
            $("#webrtc-leave").css("display","inline-flex")
            changeRTCstatus("active")
        }
        if ('wrtc' in window) {
            cb()
        } else {
            window.wrtc = new SimpleWebRTC({
                // the id/element dom element that will hold "our" video
                localVideoEl: 'localVideo',
                // the id/element dom element that will hold remote videos
                remoteVideosEl: 'remotesVideos',
                // immediately ask for camera access
                autoRequestMedia: true,
                url: 'https://sockets.lunavod.ru/'
            });
            wrtc.on('readyToCall', cb)
        }
    })
    $("#webrtc-leave").click(function(){
        wrtc.leaveRoom('your awesome room name')
        sock.emit("leaveRTC")
        $("#webrtc-join").css("display","inline-flex")
        $("#webrtc-leave").css("display","none")
        changeRTCstatus("inactive")
    })
    window.rtcUsers = []
    sock.on('user joined', updateRTCusers)
    sock.on('user leaved', updateRTCusers)
    sock.on('rtc users',updateRTCusers)
    sock.emit('getRTC')
}