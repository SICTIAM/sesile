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

const extractRootDomain = (url) => {
    var domain;
    //find & remove protocol (http, ftp, etc.) and get domain
    if (url.indexOf("://") > -1) {
        domain = url.split('/')[2];
    } else {
        domain = url.split('/')[0];
    }
    //find & remove port number
    domain = domain.split(':')[0];
    var splitArr = domain.split('.'), arrLen = splitArr.length;
    if (arrLen > 2) {
        domain = splitArr[arrLen - 2] + '.' + splitArr[arrLen - 1];
        //check to see if it's using a Country Code Top Level Domain (ccTLD) (i.e. ".me.uk")
        if (splitArr[arrLen - 2].length == 2 && splitArr[arrLen - 1].length == 2) {
            //this is using a ccTLD
            domain = splitArr[arrLen - 3] + '.' + domain;
        }
    }

    return domain;
};

export { handleErrors, DisplayLongText, BytesToSize, TruncateFileName, extractRootDomain }