const basicNotification = (type, title, message) => {
    return {
        title: title,
        message: message,
        level: type,
        position: 'tr',
        autoDismiss: 6
    }
}

module.exports = {
    basicNotification
}
