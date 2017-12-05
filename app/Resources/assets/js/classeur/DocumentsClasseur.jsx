import React, { Component } from 'react'
import { array, func, number } from 'prop-types'
import OnlyOffice from './OnlyOffice'
import Documents from './Documents'
import { translate } from 'react-i18next'
import { basicNotification } from '../_components/Notifications'
import { handleErrors } from '../_utils/Utils'

class DocumentsClasseur extends Component {

    static contextTypes = {
        t: func,
        _addNotification: func
    }

    constructor(props) {
        super(props)
        this.state = {
            documents: [],
            currentDocument: {},
            revealDisplay: "none"
        }
    }

    componentDidMount() {
        this.setState({
            documents: this.props.documents,
            currentDocument: this.props.documents[0]
        })
    }

    fetchDocuments() {
        const { t, _addNotification } = this.context
        const { classeurId } = this.props
        fetch(Routing.generate('sesile_document_documentapi_getbyclasseur', {id: classeurId}), { credentials: 'same-origin'})
            .then(handleErrors)
            .then(response => response.json())
            .then(documents => {
                this.setState({documents})
            })
            .catch(error => _addNotification(basicNotification(
                'error',
                t('admin.error.not_extractable_list', {name: t('common.documents.name_plural'), errorCode: error.status}),
                error.statusText)))
    }

    fetchDocument(id) {
        const { t, _addNotification } = this.context

        fetch(Routing.generate('sesile_document_documentapi_get', {id}), { credentials: 'same-origin'})
            .then(handleErrors)
            .then(response => response.json())
            .then(currentDocument => this.setState({currentDocument}))
            .catch(error => _addNotification(basicNotification(
                'error',
                t('admin.error.not_extractable', {name: t('common.documents.name'), errorCode: error.status}),
                error.statusText)))
    }

    onDrop = (documents) => {
        let formData  = new FormData()
        documents.map((document) => (formData.append('document[]', document)))
        formData.append('path', document)

        fetch(Routing.generate("sesile_document_documentapi_upload", {id: this.props.classeurId}), {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
            .then(response => response.json())
            .then(() => {
                this.fetchDocuments()
            })
    }

    removeDocument = (id) => {
        fetch(Routing.generate('sesile_document_documentapi_remove', {id}), {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            credentials: 'same-origin'
        })
            .then(response => response.json())
            .then(() => {
                this.fetchDocuments()
            })
    }

    handleClickDocument = (id) => {
        this.fetchDocument(id)
    }

    displayReveal = (id) => {
        this.setState({revealDisplay: 'block'})
        this.fetchDocument(id)
    }
    hideRevealDisplay = () => {
        this.setState({revealDisplay: 'none'})
    }

    render () {
        const { documents, currentDocument, revealDisplay } = this.state

        return (
            <div className="grid-x grid-y grid-frame">

                { (this.props.documents[0] && revealDisplay === "block") &&
                    <div className="reveal-full" style={{display: revealDisplay}}>
                        <div className="fi-x reveal-ico" onClick={() => this.hideRevealDisplay()}></div>
                        <OnlyOffice document={ currentDocument }/>
                    </div>
                }
                { (this.props.documents[0]  && revealDisplay === "none") && <OnlyOffice document={ currentDocument }/> }
                <Documents documents={ documents }
                           onClick={ this.handleClickDocument}
                           onDrop={ this.onDrop }
                           removeDocument={this.removeDocument}
                           displayReveal={this.displayReveal}
                />
            </div>
        )
    }
}

DocumentsClasseur.propTypes = {
    documents: array,
    classeurId: number
}

export default translate(['sesile'])(DocumentsClasseur)