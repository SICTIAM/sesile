import React from 'react'

const handleErrors = (response) => {
    if(response.status >= 200 && response.status < 300) return response
    else throw response
}

const DisplayLongText = ({text, className = "", maxSize = 20, title = ''}) =>
    <span className={className} title={title || text}>
        {text && text.length > maxSize ?
            (`${text.substring(0, maxSize)}...`) :
            (text)
        }
    </span>

const BytesToSize = (bytes) => {
    const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB']
    if (bytes === 0) return 'n/a'
    const i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)), 10)
    if (i === 0) return `${bytes} ${sizes[i]})`
    return `${(bytes / (1024 ** i)).toFixed(1)} ${sizes[i]}`
}

const TruncateFileName = (name) => {
    (name.length > 20)
        ? name = `${name.substring(0, 20)}... .${name.split('.').pop()}`
        : name = name

    return name
}

export { handleErrors, DisplayLongText, BytesToSize, TruncateFileName }