import React, { Component } from 'react'
import { translate } from 'react-i18next'
import Dropzone from 'react-dropzone'
import {array, func, string, object} from 'prop-types'
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
        const { typeClasseur, documents } = nextProps

        if (typeClasseur.nom === "Helios" && documents.length > 0 ) {
            this.setState({disabled: true, multiple: false})
        }
        else {
            this.setState({disabled: false, multiple: true})
        }

        if (typeClasseur && typeClasseur.nom === "Helios") {
            this.setState({accept: 'text/xml', multiple: false})
        } else {
            this.setState({accept: 'image/*, application/*, text/*', multiple: true})
        }

        (typeClasseur.nom === "Helios") ?
            this.setState({fileRule: t('common.documents.error.file_acceptation_rules_helios')})
            : this.setState({fileRule: t('common.documents.error.file_acceptation_rules')})
    }

    render () {

        const { t } = this.context
        const { documents, onDrop, removeDocument }  = this.props
        const { disabled, accept, multiple, fileRule } = this.state

        return (

            <div className="grid-x panel grid-padding-y">
                <div className="cell medium-12">
                    <div className="grid-x grid-margin-x grid-padding-x">
                        <div className="cell medium-12">
                            <h3>{t('common.documents.title_preview')}</h3>
                        </div>
                    </div>
                    <div className="grid-x grid-margin-x grid-padding-x">
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
                                    <GridX className="align-center">
                                        <Cell>
                                            <i className="fa fa-file"></i>
                                        </Cell>
                                        <DocumentPreview documents={documents} onClick={removeDocument} />
                                        <Cell className="medium-11 text-small">
                                            { (this.state.dropFileError)
                                                ? <span className="text-alert">{this.state.dropFileError}</span>
                                                : <span>{fileRule}</span>
                                            }
                                        </Cell>
                                    </GridX>
                                }
                            </Dropzone>
                        </div>
                    </div>
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
    typeClasseur: object.isRequired
}

export default translate('sesile')(DocumentsNew)

const DisplayFileName = ({documents, onClick}, {t}) => {
    return (
        <Cell className="align-left text-left">
            { documents.map(file => file ?
                <p key={file.name}>
                    { (file.name.length > 20)
                        ? `...${file.name.substring(file.name.length -20, file.name.length)} `
                        : file.name
                    }
                    <a style={{color: 'red', width: 0.1, height: 0.1}} onClick={(e) => onClick(e)} title={t('common.button.remove')}> x</a></p>
                : t('common.drop_file_here'))
            }
        </Cell>
    )
}

DisplayFileName.contextTypes = {
    t: func
}