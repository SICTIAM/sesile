import React, { Component } from 'react'
import { array, func, number, object } from 'prop-types'
import { translate } from 'react-i18next'
import DocumentsNew from '../document/DocumentsNew'

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
        const { documents, removeDocument, onClick, onDrop, displayReveal, typeClasseur } = this.props

        return (
            <div className="panel grid-padding-y list-documents">
                <DocumentsNew documents={documents}
                              onClick={onClick}
                              onDrop={onDrop}
                              removeDocument={removeDocument}
                              displayReveal={displayReveal}
                              typeClasseur={typeClasseur}
                />
            </div>
        )
    }
}

Documents.propTypes = {
    documents: array,
    onClick: func,
    onDrop: func,
    removeDocument: func,
    displayReveal: func,
    typeClasseur: object.isRequired
}

Documents.contextTypes = {
    t: func
}

export default translate(['sesile'])(Documents)


const Document = ({document, onClick, removeDocument, displayReveal}, {t}) => {
    return (
        <div className="grid-x">
            <div key={document.id} className="cell auto">
                <div className="grid-x text-center">

                    <div className="cell medium-12" onClick={(e) => onClick(document.id)}>
                        <span className="fa fa-file document-ico"></span>
                        <span className="fa fa-close document-remove" onClick={(e) => removeDocument(document.id)} title={ t('common.documents.delete_document')}></span>
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