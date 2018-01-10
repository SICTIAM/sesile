import React, { Component } from 'react'
import { translate } from 'react-i18next'
import {array, func, object, number} from 'prop-types'


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
        const { document, id, removeDocument }  = this.props

        return (
            <div className="cell medium-6">
                <div className="grid-x">
                    <div className="cell medium-11">
                        <DocumentType document={document} />
                    </div>
                    <div className="cell medium-1">
                        <span className="fi-x badge alert" onClick={() => removeDocument(id)} title={ t('common.documents.delete_document')}></span>
                    </div>
                </div>
                <div className="grid-x">
                    <div className="cell medium-12">
                        {document.name}
                    </div>
                </div>
                <div className="grid-x">
                    <div className="cell medium-12 text-center">
                        {this.bytesToSize(document.size)}
                    </div>
                </div>
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
    id: number,
    removeDocument: func,
    displayReveal: func
}

export default translate('sesile')(DocumentPreview)


const DocumentType = ({document}) => {

    return (
        (document.type.indexOf('image/') !== -1)
            ? <img src={document.preview}/>
            : <div className="text-center"><span className="fi-page document-ico"></span></div>
    )
}