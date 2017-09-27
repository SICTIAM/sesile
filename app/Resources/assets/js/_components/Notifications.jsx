const basicNotification = (type, title, message) => {
    return {
        title: title,
        message: message,
        level: type,
        position: 'tr',
        autoDismiss: 0
    }
}

module.exports = {
    basicNotification
}
