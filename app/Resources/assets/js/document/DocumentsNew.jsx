import React, { Component } from 'react'
import { translate } from 'react-i18next'
import Dropzone from 'react-dropzone'
import {array, func, string, object, bool} from 'prop-types'
import DocumentPreview from './DocumentPreview'
import {Cell, GridX} from '../_components/UI'


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

    render () {

        const { t } = this.context
        const { documents, onDrop, removeDocument, onClick, displayReveal, statusClasseur }  = this.props
        const { disabled, accept, multiple, fileRule } = this.state

        return (

            <div className="grid-x panel grid-padding-y">
                <div className="cell medium-12">
                    <div className="grid-x grid-margin-x grid-padding-x">
                        <div className="cell medium-12">
                            <h3>{t('common.documents.title_preview')}</h3>
                        </div>
                    </div>
                    <div className="grid-x grid-margin-x grid-padding-x grid-padding-y show-for-large">
                        <div className="cell medium-12">
                            <Dropzone
                                className="documentation-dropzone grid-x align-middle align-center"
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

                    <GridX className="align-center grid-padding-y">
                        <DocumentPreview documents={documents}
                                         remove={removeDocument}
                                         onClick={onClick}
                                         displayReveal={displayReveal}
                                         statusClasseur={statusClasseur} />
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