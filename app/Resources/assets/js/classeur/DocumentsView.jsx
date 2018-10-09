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

    static defaultProps = {
        currentDocument: {
            repourl: ''
        }
    }

    constructor(props) {
        super(props)
        this.state = {
            documents: [],
            currentDocument: {
                repourl: ''
            },
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
        this.props.documents.map(document => {
            $(`#signature-dropdown-${document.id}`).foundation()
            $(`#visa-dropdown-${document.id}`).foundation()
            $(`#signature-visa-dropdown-${document.id}`).foundation()
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
                documents && this.setState({documents})
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
        if(this.props.editClasseur) {
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
    }

    handleClickDocument = (e, id) => {
        e.preventDefault()
        e.stopPropagation()
        if(id !== this.state.currentDocument.id) this.fetchDocument(id)
    }

    displayReveal = (e, id) => {
        e.preventDefault()
        e.stopPropagation()
        this.setState({revealDisplay: 'block'})
        if(id !== this.state.currentDocument.id) this.fetchDocument(id)
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
    isXmlFileType = (document) => document.type && document.type === "text/xml"
    render () {
        const { t } = this.context
        const { documents, currentDocument, revealDisplay, user } = this.state
        const { classeurType, status, editClasseur } = this.props
        const onlyOfficeType = ['docx', 'doc', 'xlsx', 'xls', 'ppt', 'pptx']
        const imageType = ['png', 'jpg', 'jpeg', 'gif']
        const heliosType = ['xml']
        let fileType
        currentDocument && currentDocument.repourl ? fileType = currentDocument.repourl.split('.').pop() : fileType = ""
        return (
            <div>
                {currentDocument &&
                <div className="grid-x panel">
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

                    { this.isXmlFileType(currentDocument) && currentDocument.id && revealDisplay === "none" &&
                        <div>
                            <Helios document={ currentDocument } />
                        </div>
                    }
                    { this.isXmlFileType(currentDocument) && currentDocument.id && revealDisplay === "block" &&
                        <div className="reveal-full" style={{display: revealDisplay}}>
                            <div className="fa fa-close reveal-ico" onClick={() => this.hideRevealDisplay()}></div>
                            <Helios document={ Object.assign({}, currentDocument) } />
                        </div>
                    }
                    {(currentDocument.type === 'application/pdf') &&
                        <iframe
                            id={currentDocument.name}
                            style={{
                                padding: 0,
                                border: '5px'}}
                            className="cell medium-12 only-office-height"
                            src={`./../uploads/docs/${currentDocument.repourl}`}>
                            {t('common.browser_not_support_pdf')}, <a src={`./../uploads/docs/${currentDocument.repourl}`}>{t('common.download_pdf')}</a>
                        </iframe>}
                    { (onlyOfficeType.includes(fileType) && currentDocument.repourl && revealDisplay === "block" && user.id ) &&
                        <div className="reveal-full" style={{display: revealDisplay}}>
                            <div className="fa fa-close reveal-ico" onClick={() => this.hideRevealDisplay()}></div>
                            <OnlyOffice document={ currentDocument } user={user} revealDisplay={true} />
                        </div>
                    }
                    { (onlyOfficeType.includes(fileType) && currentDocument.repourl && revealDisplay === "none" && user.id) &&
                        <OnlyOffice document={ currentDocument } user={user} revealDisplay={false} />
                    }
                </div>}
                <DocumentsNew
                    user={this.props.user}
                    documents={documents}
                    onClick={this.handleClickDocument}
                    onDrop={this.onDrop}
                    removeDocument={this.removeDocument}
                    displayReveal={this.displayReveal}
                    typeClasseur={classeurType}
                    statusClasseur={status}
                    classeurId={this.props.classeurId}
                    editClasseur={editClasseur}
                    isHeliosAndNewClasseur={this.isHeliosAndNewClasseur}/>
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