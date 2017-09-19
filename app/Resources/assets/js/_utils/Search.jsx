const escapedValue = (value, filteredArray, array) => {
    const escapedValue = value.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')

    if (escapedValue === '') {
        filteredArray = array
    }

    return RegExp(escapedValue, 'i')
}

module.exports = { escapedValue }