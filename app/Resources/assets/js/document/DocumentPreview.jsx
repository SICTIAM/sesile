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

    fileName(name) {
        let fileName
        (name.length > 20)
            ? fileName = `${name.substring(0, 20)}... .${name.split('.').pop()}`
            : fileName = name

        return fileName
    }

    render () {

        const { t } = this.context
        const { documents, remove, onClick, displayReveal }  = this.props

        return (
            <div className="cell medium-11 text-left">
                { documents.map(file => file ?
                    <div key={file.name} className="grid-x align-middle grid-margin-y">
                        { file.id ?
                            <div className="cell medium-5" onClick={(e) => onClick(e, file.id)}>
                                { this.fileName(file.name) }
                            </div>
                            :
                            <div className="cell medium-11" >
                                { this.fileName(file.name) }
                                { file.size && ' - ' + this.bytesToSize(file.size) }
                            </div>
                        }

                        { file.id &&
                            <div className="cell medium-1">
                                <i className="icon-action fa fa-arrows-alt" onClick={(e) => displayReveal(e, file.id)} aria-hidden="true"></i>
                            </div>
                        }

                        { file.id && file.type === "application/pdf" &&
                        <div className="cell medium-5 document-name">
                            <a className="button" href={Routing.generate('download_doc', {id: file.id})} target="_blank">{ t('common.documents.btn_origin')}</a>
                            <a className="button" href={Routing.generate('download_doc_visa', {id: file.id})} target="_blank">{ t('common.documents.btn_visa')}</a>
                            <a className="button" href={Routing.generate('download_doc_sign', {id: file.id})} target="_blank">{ t('common.documents.btn_signature')}</a>
                            <a className="button" href={Routing.generate('download_doc_all', {id: file.id})} target="_blank">{ t('common.documents.btn_both')}</a>
                        </div>
                        }
                        <div className="cell medium-1 text-center">
                            <span className="icon-action fa fa-trash" onClick={(e) => remove(e, file.id)} title={t('common.button.remove')}></span>
                        </div>
                    </div>
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
    remove: func,
    onClick: func,
    displayReveal: func
}

export default translate('sesile')(DocumentPreview)