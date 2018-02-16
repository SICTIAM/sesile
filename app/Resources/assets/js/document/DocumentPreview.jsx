import React, { Component } from 'react'
import { translate } from 'react-i18next'
import {func, object} from 'prop-types'


class DocumentPreview extends Component {

    static contextTypes = {
        t: func
    }

    bytesToSize(bytes) {
        const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB']
        if (bytes === 0) return 'n/a'
        const i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)), 10)
        if (i === 0) return `${bytes} ${sizes[i]})`
        return `${(bytes / (1024 ** i)).toFixed(1)} ${sizes[i]}`
    }

    render () {

        const { t } = this.context
        const { documents, onClick }  = this.props

        return (
            <div className="cell medium-11 text-left">
                { documents.map(file => file ?
                    <p key={file.name}>
                        { (file.name.length > 20)
                            ? `...${file.name.substring(file.name.length -20, file.name.length)}`
                            : file.name
                        }
                        &nbsp;- {this.bytesToSize(file.size)}
                        <a style={{color: 'red', width: 0.1, height: 0.1}} onClick={(e) => onClick(e)} title={t('common.button.remove')}> x</a></p>
                    : t('common.drop_file_here'))
                }
            </div>
        )
    }
}

DocumentPreview.contextTypes = {
    t: func
}

DocumentPreview.propTypes = {
    document: object,
    onClick: func,
}

export default translate('sesile')(DocumentPreview)