import React from 'react'

const handleErrors = (response) => {
    if(response.status >= 200 && response.status < 300) return response
    else throw response
}

const DisplayLongText = ({text, className = "", maxSize = 20}) => 
    <span className={className} title={text}>
        {text.length > maxSize ?
            (`${text.substring(0, maxSize)}...`) :
            (text)
        }
    </span>

export { handleErrors, DisplayLongText }