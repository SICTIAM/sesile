const basicNotification = (type, title, message, autoDismiss = 6) => {
    return {
        title: title,
        message: message,
        level: type,
        position: 'tr',
        autoDismiss: autoDismiss
    }
}

module.exports = {
    basicNotification
}
