import React, { Component } from 'react'
import { array, func, number, object } from 'prop-types'
import Dropzone from 'react-dropzone'
import { translate } from 'react-i18next'

class Documents extends Component {

    static contextTypes = {
        t: func
    }

    constructor(props) {
        super(props)
        this.state = {
            documents: [],
            currentDocument: {},
            revealDisplay: "none"
        }
    }

    render () {
        const { t } = this.context
        const { documents, removeDocument, onClick, onDrop, displayReveal } = this.props

        return (
            <div className="cell medium-3 list-documents">
                <div className="grid-y grid-frame">
                    <div className="cell medium-9">
                        <div className="grid-x">
                            {
                                documents.map((document) =>
                                    <Document key={document.id}
                                        document={document}
                                        onClick={onClick}
                                        removeDocument={removeDocument}
                                        displayReveal={displayReveal}
                                    />
                                )
                            }
                        </div>
                    </div>

                    <Dropzone onDrop={(e) => onDrop(e)} className="dropzone cell medium-3 text-center align-center-middle">
                        <h4>{ t('common.documents.label_dropzone') }</h4>
                    </Dropzone>

                </div>
            </div>
        )
    }
}

Documents.propTypes = {
    documents: array,
    onClick: func,
    onDrop: func,
    removeDocument: func,
    displayReveal: func
}

Documents.contextTypes = {
    t: func
}

export default translate(['sesile'])(Documents)


const Document = ({document, onClick, removeDocument, displayReveal}, {t}) => {
    return (
        <div key={document.id} className="cell auto">
            <div className="grid-x text-center">

                <div className="cell medium-12" onClick={(e) => onClick(document.id)}>
                    <span className="fi-page document-ico"></span>
                    <span className="fi-x document-remove" onClick={(e) => removeDocument(document.id)} title={ t('common.documents.delete_document')}></span>
                </div>
                <div className="cell medium-12 document-name" onClick={(e) => onClick(document.id)}>
                    {document.name}
                </div>
                <div className="cell medium-12 document-name" onClick={(e) => displayReveal(document.id)}>
                    <button className="button">{ t('common.documents.full_screen')}</button>
                </div>
            </div>
            <div className="grid-x text-center">
                { document.type === "application/pdf" &&
                <div className="cell medium-12 document-name">
                    <a className="button" href={Routing.generate('download_doc', {id: document.id})} target="_blank">{ t('common.documents.btn_origin')}</a>
                    <a className="button" href={Routing.generate('download_doc_visa', {id: document.id})} target="_blank">{ t('common.documents.btn_visa')}</a>
                    <a className="button" href={Routing.generate('download_doc_sign', {id: document.id})} target="_blank">{ t('common.documents.btn_signature')}</a>
                    <a className="button" href={Routing.generate('download_doc_all', {id: document.id})} target="_blank">{ t('common.documents.btn_both')}</a>
                </div>
                }
            </div>
        </div>
    )
}

Document.propTypes = {
    document: object,
    onClick: func,
    removeDocument: func,
    displayReveal:func
}

Document.contextTypes = {
    t: func
}