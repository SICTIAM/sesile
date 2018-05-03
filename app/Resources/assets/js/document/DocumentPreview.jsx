import React, { Component } from 'react'
import { translate } from 'react-i18next'
import {func, object, number} from 'prop-types'


class DocumentPreview extends Component {

    static contextTypes = {
        t: func
    }

    componentDidUpdate () {
        $('#download-documents').foundation()
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
        const { documents, remove, onClick, displayReveal, statusClasseur }  = this.props

        return (
            <div className="cell medium-11 text-left">
                { documents.map(file => file ?
                    <div className="cell medium-3 panel">
                        <div key={file.name} className="grid-x align-middle grid-margin-y grid-margin-x">
                            { file.id ?
                                <div className="cell small-8 medium-5" onClick={(e) => onClick(e, file.id)}>
                                    { this.fileName(file.name) }
                                </div>
                                :
                                <div className="cell small-10" >
                                    { this.fileName(file.name) }
                                    { file.size && ' - ' + this.bytesToSize(file.size) }
                                </div>
                            }

                            { file.id &&
                            <div className="cell small-2 medium-1 text-center">
                                <i className="icon-action fa fa-arrows-alt" onClick={(e) => displayReveal(e, file.id)} aria-hidden="true"></i>
                            </div>
                            }

                            <div className="cell small-2 medium-1 text-center">
                                <span className="icon-action fa fa-trash" onClick={(e) => remove(e, file.id)} title={t('common.button.remove')}></span>
                            </div>

                            { file.id && file.type === "application/pdf" &&
                            <div className="cell small-12 medium-5 document-name">
                                <div className="grid-x align-center-middle" id="download-documents" data-toggle="documents-infos">
                                    <ul className="dropdown menu" data-dropdown-menu>
                                        <li>
                                            <a href="#" className="button primary hollow">Téléchargement du document</a>
                                            <ul className="menu">
                                                <li><a className="button secondary clear" href={Routing.generate('download_doc', {id: file.id})} target="_blank">{ t('common.documents.btn_origin')}</a></li>
                                                { statusClasseur === 2 &&
                                                <div>
                                                    <hr/>
                                                    <li><a className="button secondary clear" href={Routing.generate('download_doc_visa', {id: file.id})} target="_blank">{ t('common.documents.btn_visa')}</a></li>
                                                    <hr/>
                                                    <li><a className="button secondary clear" href={Routing.generate('download_doc_sign', {id: file.id})} target="_blank">{ t('common.documents.btn_signature')}</a></li>
                                                    <hr/>
                                                    <li><a className="button secondary clear" href={Routing.generate('download_doc_all', {id: file.id})} target="_blank">{ t('common.documents.btn_both')}</a></li>
                                                </div>
                                                }

                                            </ul>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            }
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
    displayReveal: func,
    statusClasseur: number
}

export default translate('sesile')(DocumentPreview)