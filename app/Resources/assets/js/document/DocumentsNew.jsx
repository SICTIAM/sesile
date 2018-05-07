import React, { Component } from 'react'
import { translate } from 'react-i18next'
import Dropzone from 'react-dropzone'
import {array, func, string, object, bool} from 'prop-types'
import DocumentPreview from './DocumentPreview'
import {Cell, GridX} from '../_components/UI'
import { BytesToSize } from '../_utils/Utils'


class DocumentsNew extends Component {

    static contextTypes = {
        t: func
    }

    state = {
        disabled: true,
        accept: '',
        dropFileError: '',
        multiple: false,
        fileRule : ''
    }

    componentWillReceiveProps(nextProps) {
        const { t } = this.context
        const { typeClasseur, documents, editClasseur } = nextProps

        if (typeClasseur.nom === "Helios" && documents.length > 0 || !editClasseur) {
            this.setState({disabled: true})
        }
        else if (!editClasseur){
            this.setState({disabled: true})
        }
        else {
            this.setState({disabled: false})
        }

        if (typeClasseur && typeClasseur.nom === "Helios") {
            this.setState({accept: 'text/xml', multiple: false})
            this.setState({fileRule: t('common.documents.error.file_acceptation_rules_helios')})
        } else {
            this.setState({accept: 'image/*, application/*, text/*', multiple: true})
            this.setState({fileRule: t('common.documents.error.file_acceptation_rules')})
        }
    }
    isHeliosAndNewClasseur = () => {
        return this.props.classeurId && this.props.typeClasseur.nom === "Helios"
    }
    isNewClasseur = () => this.props.classeurId

    render () {

        const { t } = this.context
        const { documents, onDrop, removeDocument, onClick, displayReveal, statusClasseur }  = this.props
        const { disabled, accept, multiple, fileRule } = this.state
        const docs = this.props.documents.map((document, key) =>
            <div
                key={key}
                className=""
                style={{
                    width: '290px',
                    display: 'inline-block',
                    marginRight: '1em',
                    marginBottom: '1em',
                    boxShadow: '0 1px 2px 0 rgba(34, 36, 38, 0.15)',
                    borderRadius: '0.28571429rem',
                    border: '1px solid rgba(34, 36, 38, 0.15'}}>
                <div className="grid-x" title={document.name} style={{padding: '0.5em'}}>
                    <div className="cell medium-2 align-middle" style={{display: 'flex'}}>
                        <i className="fa fa-file-o" style={{fontSize: '2em'}}></i>
                    </div>
                    <div className="cell medium-auto">
                        <GridX>
                            <Cell className="text-truncate">
                                <span className="text-bold" style={{fontSize: '0.9em'}}>{document.name}</span>
                            </Cell>
                        </GridX>
                        <GridX>
                            <Cell>
                                <span style={{fontSize: '1em', color: 'rgba(0,0,0,0.4)'}}>{`${BytesToSize(document.size)} - ${document.name.split('.').pop()}`}</span>
                            </Cell>
                        </GridX>
                    </div>
                    {!this.isHeliosAndNewClasseur() &&
                    <Cell className="medium-1">
                        <i
                            className="fa fa-times hover-red"
                            onClick={(e) => removeDocument(e, document.id)}
                            style={{fontSize: '1em'}}/>
                    </Cell>}
                </div>
                <hr style={{margin: 0}}/>
                <div className="grid-x">
                    <div
                        className="cell medium-auto align-center doc-action-button"
                        style={{display: 'flex'}}>
                        <i className="fa fa-expand" style={{padding: '5px'}}/>
                    </div>
                    <div
                        className="cell medium-auto align-center doc-action-button"
                        style={{display: 'flex', borderLeft: 'solid 1px #b3b2b2'}}>
                        <i className="fa fa-download" style={{padding: '5px'}}/>
                    </div>
                    <div
                        className={
                            `cell medium-auto align-center doc-action-button ${!this.isHeliosAndNewClasseur() && 'disabled'}`}
                        style={{display: 'flex', borderLeft: 'solid 1px #b3b2b2'}}
                        onClick={(e) => removeDocument(e, document.id)}>
                        <i className="fa fa-trash" style={{padding: '5px'}}/>
                    </div>
                </div>
            </div>)
        return (

            <div className="grid-x panel grid-padding-y">
                <div className="cell medium-12">
                    <div className="grid-x grid-margin-x grid-padding-x">
                        <div className="cell medium-12">
                            <h3>{t('common.documents.title_preview')}</h3>
                        </div>
                    </div>
                    {statusClasseur !== 2 &&
                        <div className="grid-x grid-margin-x grid-padding-x grid-padding-y">
                            <div className="cell medium-12">
                                <Dropzone
                                    className="documentation-dropzone disabled grid-x align-middle align-center"
                                    accept={accept}
                                    multiple={multiple}
                                    name="file"
                                    disabled={disabled}
                                    maxSize={104857600}
                                    onDropRejected={(files) =>
                                        this.setState({dropFileError: fileRule})}
                                    onDropAccepted={files => onDrop(files)}>
                                    {
                                        <Cell>
                                            <GridX className="align-center grid-margin-y grid-padding-y">
                                                <Cell><i className="fa fa-file"></i></Cell>
                                            </GridX>

                                            <GridX className="align-center">
                                                <Cell className="medium-11 text-small">
                                                    { (this.state.dropFileError)
                                                        ? <span className="text-alert">{this.state.dropFileError}</span>
                                                        : <span>{fileRule}</span>
                                                    }
                                                </Cell>
                                            </GridX>

                                        </Cell>
                                    }
                                </Dropzone>
                            </div>
                        </div>
                    }
                    <GridX className="grid-margin-x grid-padding-x grid-padding-y">
                        <Cell>
                            <div className="">
                                {docs}
                            </div>
                        </Cell>
                    </GridX>
                </div>
            </div>
        )
    }
}

DocumentsNew.contextTypes = {
    t: func
}

DocumentsNew.propTypes = {
    documents: array,
    onClick: func,
    onDrop: func,
    removeDocument: func,
    displayReveal: func,
    typeClasseur: object.isRequired,
    editClasseur: bool
}

export default translate('sesile')(DocumentsNew)