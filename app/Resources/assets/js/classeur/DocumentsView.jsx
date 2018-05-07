import React, { Component } from 'react'
import { array, func, number, object, bool } from 'prop-types'
import OnlyOffice from '../document/OnlyOffice'
import { translate } from 'react-i18next'
import { basicNotification } from '../_components/Notifications'
import { handleErrors } from '../_utils/Utils'
import Helios from '../document/Helios'
import DocumentsNew from '../document/DocumentsNew'

class DocumentsView extends Component {

    static contextTypes = {
        t: func,
        _addNotification: func
    }

    constructor(props) {
        super(props)
        this.state = {
            documents: [],
            currentDocument: {},
            revealDisplay: "none",
            user: {}
        }
    }

    componentDidMount() {
        this.fetchUser()
        this.setState({
            documents: this.props.documents,
            currentDocument: this.props.documents[0]
        })
    }

    componentWillReceiveProps(nextProps) {
        this.setState({documents: nextProps.documents, currentDocument: nextProps.documents[0]})
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
            .then(documents => this.setState({documents}))
    }

    removeDocument = (e, id) => {
        e.preventDefault()
        e.stopPropagation()
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

    handleClickDocument = (e, id) => {
        e.preventDefault()
        e.stopPropagation()
        this.fetchDocument(id)
    }

    displayReveal = (e, id) => {
        e.preventDefault()
        e.stopPropagation()
        this.setState({revealDisplay: 'block'})
        this.fetchDocument(id)
    }
    hideRevealDisplay = () => {
        this.setState({revealDisplay: 'none'})
    }

    fetchUser() {
        fetch(Routing.generate("sesile_user_userapi_getcurrent"), {credentials: 'same-origin'})
            .then(response => response.json())
            .then(json => {
                this.setState({user: json})
            })
    }

    render () {
        const { documents, currentDocument, revealDisplay, user } = this.state
        const { classeurType, status, editClasseur } = this.props
        const onlyOfficeType = ['docx', 'doc', 'xlsx', 'xls', 'pdf', 'ppt', 'pptx']
        const imageType = ['png', 'jpg', 'jpeg', 'gif']
        const heliosType = ['xml']
        let fileType
        currentDocument.repourl ? fileType = currentDocument.repourl.split('.').pop() : fileType = ""

        return (
            <div>
                <DocumentsNew
                    documents={documents}
                    onClick={this.handleClickDocument}
                    onDrop={ this.onDrop }
                    removeDocument={this.removeDocument}
                    displayReveal={this.displayReveal}
                    typeClasseur={classeurType}
                    statusClasseur={status}
                    classeurId={this.props.classeurId}
                    editClasseur={editClasseur}
                    isHeliosAndNewClasseur={this.isHeliosAndNewClasseur}/>
                <div className="grid-x panel grid-padding-y">
                    { (imageType.includes(fileType) && currentDocument.repourl && revealDisplay === "block" ) &&
                        <div className="reveal-full" style={{display: revealDisplay}}>
                            <div className="fa fa-close reveal-ico" onClick={() => this.hideRevealDisplay()}></div>
                            <img src={"./../uploads/docs/" + currentDocument.repourl} className="imgPreview" />
                        </div>
                    }
                    { (imageType.includes(fileType) && currentDocument.repourl && revealDisplay === "none") &&
                        <div className="cell medium-12">
                            <img src={"./../uploads/docs/" + currentDocument.repourl} />
                        </div>
                    }

                    { heliosType.includes(fileType) && currentDocument.id && revealDisplay === "none" && classeurType.nom === "Helios" &&
                        <Helios document={ currentDocument } />
                    }
                    { heliosType.includes(fileType) && currentDocument.id && revealDisplay === "block" && classeurType.nom === "Helios" &&
                        <div className="reveal-full" style={{display: revealDisplay}}>
                            <div className="fa fa-close reveal-ico" onClick={() => this.hideRevealDisplay()}></div>
                            <Helios document={ Object.assign({}, currentDocument) } />
                        </div>
                    }


                    { (onlyOfficeType.includes(fileType) && currentDocument.repourl && revealDisplay === "block" && user.id ) &&
                        <div className="reveal-full" style={{display: revealDisplay}}>
                            <div className="fa fa-close reveal-ico" onClick={() => this.hideRevealDisplay()}></div>
                            <OnlyOffice document={ currentDocument } user={user} revealDisplay={true} />
                        </div>
                    }
                    { (onlyOfficeType.includes(fileType) && currentDocument.repourl && revealDisplay === "none" && user.id) &&
                        <OnlyOffice document={ currentDocument } user={user} revealDisplay={false} />
                    }
                </div>
            </div>
        )
    }
}

DocumentsView.propTypes = {
    documents: array,
    classeurId: number,
    classeurType: object,
    status: number,
    editClasseur: bool
}

export default translate(['sesile'])(DocumentsView)