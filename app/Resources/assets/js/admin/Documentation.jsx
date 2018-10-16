import React, { Component } from 'react'
import { func } from 'prop-types'
import { translate } from 'react-i18next'
import Dropzone from 'react-dropzone'
import Validator from 'validatorjs'

import {AdminDetails, AdminPage, SimpleContent} from '../_components/AdminUI'
import {Form, Button, Switch} from '../_components/Form'
import { basicNotification } from "../_components/Notifications"
import InputValidation from '../_components/InputValidation'
import { GridX, Cell } from '../_components/UI'
import { handleErrors } from '../_utils/Utils'
import History from '../_utils/History'

class Documentation extends Component {
    state = {
        editState: false,
        files: [],
        dropFileError: '',
        sending: false,
        type: '',
        switch: false,
        documentation: {
            id: null,
            description: '',
            version: '',
            date: null,
            file: null
        }
    }
    static contextTypes = {
        t: func,
        _addNotification: func
    }
    validationRules = {
        description: 'required|string',
        version: 'required|numeric|min:1|max:100',
        file: 'required'
    }
    type = Object.freeze({help: 'aide', update: 'mise-a-jour'})

    componentDidMount() {
        if (this.props.match.params.type) this.setState({type: this.props.match.params.type})
        else this.setState({type: this.type.update})
        if (this.state.type === this.type.help) this.setState({switch: true})
        if (this.props.match.params.id) this.fetchDocumentation(this.props.match.params.id)
    }

    validationHelp(fields) {
        return new Validator(
            fields,
            {description: this.validationRules.description, file: this.validationRules.file},
            {'required.file': this.context.t('common.file_is_required')})
    }

    validationPatch(fields) {
        return new Validator(
            fields,
            {
                description: this.validationRules.description,
                version: this.validationRules.version,
                file: this.validationRules.file
            },
            {'required.file': this.context.t('common.file_is_required')})
    }

    validationErrorFile(validation) {
        const errorMassage = validation.errors.first('file')
        this.setState({dropFileError: errorMassage})
    }

    fetchDocumentation(id) {
        const {t, _addNotification} = this.context
        fetch(
            Routing.generate(
                `sesile_main_documentationapi_${(this.props.match.params.type == this.type.help) ? 'showaide' : 'showpatch' }`,
                {id}),
            {credentials: 'same-origin'})
            .then(handleErrors)
            .then(response => response.json())
            .then(documentation => this.setState({documentation}))
            .catch(error =>
                _addNotification(basicNotification('error', t('admin.documentations.error.fetch'))))
    }

    saveDocumentation = () => {
        if (this.state.type === this.type.help) this.saveOrUpdateHelp()
        else if (this.state.type === this.type.update) this.saveOrUpdatePatch()
    }
    saveOrUpdateHelp = () => {
        const {t, _addNotification} = this.context
        const fields = {
            id: this.state.documentation.id,
            description: this.state.documentation.description,
            file: this.state.documentation.file
        }
        const validation = this.validationHelp(fields)
        if (validation.passes()) {
            const data = new FormData()
            if (fields.file.name != this.state.documentation.path) data.append('file', fields.file)
            data.append('description', fields.description)
            this.setState({sending: true, editState: false})
            fetch(
                Routing.generate(
                    `sesile_main_documentationapi_${(this.props.match.params.id) ? 'updateaide' : 'postaide' }`,
                    {id: this.props.match.params.id}),
                {method: 'POST', body: data, credentials: 'same-origin'})
                .then(handleErrors)
                .then(response => response.json())
                .then(help => {
                    History.push('/admin/documentations')
                    _addNotification(
                        basicNotification(
                            'success',
                            this.context.t('admin.documentations.success_save')))
                })
                .catch(error =>
                    _addNotification(
                        basicNotification(
                            'error',
                            this.context.t('admin.documentations.error.save'))))
                .finally(() => this.setState({sending: false, editState: true}))
        } else this.validationErrorFile(validation)
    }

    saveOrUpdatePatch() {
        const {t, _addNotification} = this.context
        const fields = {
            id: this.state.documentation.id,
            description: this.state.documentation.description,
            version: this.state.documentation.version,
            file: this.state.documentation.file
        }
        const validation = this.validationPatch(fields)
        if (validation.passes()) {
            const data = new FormData()
            if (fields.file.name != this.state.documentation.path) data.append('file', fields.file)
            data.append('version', fields.version)
            data.append('description', fields.description)
            this.setState({sending: true, editState: false})
            fetch(
                Routing.generate(
                    `sesile_main_documentationapi_${(this.props.match.params.id) ? 'updatepatch' : 'postpatch' }`,
                    {id: this.props.match.params.id}),
                {method: 'POST', body: data, credentials: 'same-origin'})
                .then(handleErrors)
                .then(response => response.json())
                .then(patch => {
                    History.push('/admin/documentations')
                    _addNotification(
                        basicNotification(
                            'success',
                            this.context.t('admin.documentations.success_save')))
                })
                .catch(error =>
                    _addNotification(
                        basicNotification(
                            'error',
                            this.context.t('admin.documentations.error.save'))))
                .finally(() => this.setState({sending: false, editState: true}))
        } else this.validationErrorFile(validation)
    }

    handleChangeDocumentation = (key, value) => {
        this.setState(prevState => {
            documentation: prevState.documentation[key] = value
        })
        if (value) this.setState({editState: true})
        else this.setState({editState: false})
    }
    handleDropFile = (files) => {
        this.setState({dropFileError: ''})
        this.handleChangeDocumentation('file', files[0])
        console.log(this.state.type)
    }
    handleChangeType = () => {
        const {type} = this.state
        if (type === this.type.help) {
            this.setState({type: this.type.update})
            this.setState({switch: false})
        }
        else if (type === this.type.update) {
            this.setState({type: this.type.help})
            this.setState({switch: true})
        }
    }
    handleRemoveFile = (e) => {
        e.preventDefault()
        e.stopPropagation()
        this.handleChangeDocumentation('file', null)
    }

    render() {
        const {t} = this.context
        const {documentation, editState} = this.state
        return (
            <Form onSubmit={this.Documentation}>
                <AdminPage>
                    <div className="cell medium-12 text-center" style={{marginBottom: "1.3em"}}>
                        <h2 className="text-capitalize-first-letter">{(this.state.type === this.type.help) ? t('admin.documentations.help') : t('admin.documentations.update')}</h2>
                    </div>
                    <SimpleContent className="panel">
                        <GridX className="grid-padding-x grid-padding-y">
                            <Cell className="medium-6">
                                <GridX>
                                    <Cell>
                                        <InputValidation
                                            id="description"
                                            type="text"
                                            className=""
                                            autoFocus={true}
                                            labelText={`${t('common.label.description')} *`}
                                            value={documentation.description}
                                            onChange={this.handleChangeDocumentation}
                                            validationRule={this.validationRules.description}
                                            placeholder={t('admin.documentations.placeholder_description')}/>
                                    </Cell>
                                    {(this.state.type === this.type.update) &&
                                    <Cell>
                                        <InputValidation
                                            id="version"
                                            type="text"
                                            className=""
                                            labelText={`${t('common.label.version')} *`}
                                            value={documentation.version}
                                            onChange={this.handleChangeDocumentation}
                                            validationRule={this.validationRules.version}
                                            placeholder={t('admin.documentations.placeholder_version')}/>
                                    </Cell>
                                    }
                                </GridX>
                            </Cell>
                            <Cell className="medium-6">
                                {!this.props.match.params.id &&
                                <div style={{ marginBottom:"1em"}}>
                                    <label className="text-bold text-capitalize-first-letter">Type</label>
                                    <div className="pretty p-default p-round" style={{marginTop:"1em"}}>
                                        <input type="radio" name="radio1" checked={this.state.switch}
                                               onChange={this.handleChangeType}/>
                                        <div className="state  p-primary-o">
                                            <label/>
                                            <span>Aide</span>
                                        </div>
                                    </div>
                                    <div className="pretty p-default p-round">
                                        <input type="radio" name="radio1" checked={!this.state.switch}
                                               onChange={this.handleChangeType}/>
                                        <div className="state  p-primary-o">
                                            <label/>
                                            <span>Patch</span>
                                        </div>
                                    </div>
                                </div>
                                }
                                <Dropzone
                                    className="documentation-dropzone grid-x align-middle align-center"
                                    accept="application/pdf"
                                    multiple={false}
                                    name="file"
                                    maxSize={20971520}
                                    onDropRejected={(files) =>
                                        this.setState({dropFileError: t('admin.documentations.error.file_acceptation_rules')})}
                                    onDropAccepted={files => this.handleDropFile(files)}>
                                    {<GridX>
                                        <Cell>
                                            <i className="fa fa-file-pdf-o"></i>
                                        </Cell>
                                        <DisplayFileName documentation={documentation} onClick={this.handleRemoveFile}/>
                                        <Cell className="text-small">
                                            {(this.state.dropFileError) ?
                                                <span style={{color: 'red'}}>{this.state.dropFileError}</span> :
                                                <span>{t('admin.documentations.error.file_acceptation_rules')}</span>}
                                        </Cell>
                                    </GridX>}
                                </Dropzone>
                            </Cell>
                        </GridX>
                    </SimpleContent>
                    <Cell>
                        <GridX>
                            <Button
                                disabled={!editState}
                                classNameButton="primary"
                                className="cell medium-12 text-right"
                                onClick={this.saveDocumentation}
                                labelText=
                                    {this.props.match.params.id ?
                                        t('common.button.edit_save') :
                                        t('common.button.save')}
                                loading={this.state.sending}/>
                        </GridX>
                    </Cell>
                </AdminPage>
            </Form>
        )
    }
}

export default translate(['sesile'])(Documentation)

const DisplayFileName = ({documentation, onClick}, {t}) => {
    const { file } = documentation
    return (
        <Cell className="text-bold">
            {(documentation.file) ?
                <p key={file.name} title={file.name}>
                {(file.name.split('.').shift().length > 30) ?
                    `${file.name.split('.').shift().substring(0,30)}...${file.name.split('.').pop()} `:
                    file.name}
                <a style={{color: 'red', width: 0.1, height: 0.1}} onClick={(e) => onClick(e)} title={t('common.button.remove')}> x</a></p> :
                t('common.drop_file_here')}
        </Cell>
    )
}

DisplayFileName.contextTypes = {
    t: func
}