import React, { Component } from 'react'
import { func, object, number, string, bool } from 'prop-types'
import { translate } from 'react-i18next'
import Validator from 'validatorjs'
import { handleErrors } from '../_utils/Utils'
import { basicNotification } from '../_components/Notifications'
import InputValidation from '../_components/InputValidation'
import { InputFile, Avatar, Switch, Button, ButtonConfirm } from '../_components/Form'
import { AccordionItem } from '../_components/AdminUI'

class CollectiviteInfos extends Component {

    static contextTypes = {
        t: func,
        _addNotification: func
    }

    static propTypes = {
        id: number,
        nom: string.isRequired,
        image: string,
        active: bool.isRequired,
        delete_classeur_after: number.isRequired,
        handleChange: func.isRequired,
        putCollectivite: func.isRequired,
        editState: bool.isRequired
    }

    state = { isFormValid: false }

    validationRules = {
        nom: 'required',
        delete_classeur_after: 'required|min:10|max:365'
    }

    customErrorMessages = {
        delete_classeur_after: {min: this.context.t('admin.collectivite.error.field_delay_min'), 
                                max: this.context.t('admin.collectivite.error.field_delay_max'),
                                required: this.context.t('admin.collectivite.error.field_delay_required')}
    }

    updateAvatar = (file) => {
        if (file) {
            const data = new FormData()
            data.append('image', file)
            fetch(Routing.generate('sesile_main_collectiviteapi_uploadavatar', {id: this.props.id}), {
                credentials: 'same-origin',
                method: 'POST',
                body: data
            })
            .then(handleErrors)
            .then(response => response.json())
            .then(json => {
                this.context._addNotification(basicNotification(
                    'success', 
                    this.context.t('admin.collectivite.success_upload_avatar')))
                this.props.handleChange('image', json.image)}
            )
            .catch(error => this.context._addNotification(basicNotification(
                'error', 
                this.context.t('admin.collectivite.error.upload_avatar', {errorCode: error.status}), 
                error.statusText)))
        }
    }

    deleteAvatar = () => {
        const { id } = this.props
        fetch(Routing.generate('sesile_main_collectiviteapi_deleteavatar', {id}), {
            credentials: 'same-origin',
            method: 'DELETE'
        })
        .then(this.handleErrors)
        .then(response => response.json())
        .then(json => {
            $("#confirm_delete").foundation('close')
            this.props.handleChange('image', json.image)
        })
        .catch(error => this.context._addNotification(basicNotification(
            'error', 
            this.context.t('admin.collectivite.error.delete_avatar', {errorCode: error.status}), 
            error.statusText)))
    }

    saveCollectivite = () => {
        const { putCollectivite, id, nom, active, delete_classeur_after, editState } = this.props
        const fields = { nom, active, delete_classeur_after }
        const validation = new Validator(fields, this.validationRules)
        if(validation.passes()) if(id) putCollectivite(id, fields)
    }

    render() {
        const { t } = this.context
        const { id, nom, image, active, delete_classeur_after, handleChange, putCollectivite, editState } = this.props
        return (
            <AccordionItem className="is-active" title={t('admin.collectivite.infos')}>
                <div className="medium-6 cell">
                    <div className="grid-x grid-padding-y">
                        <Avatar className="cell medium-12" size={200} nom={nom} fileName={image}/>
                        <InputFile  id="add_collectivite_img"
                                    className="columns medium-3"
                                    labelText={image ? t('common.button.change_img') : t('common.button.upload_img')}
                                    accept="image/png,image/jpeg"
                                    onChange={this.updateAvatar}/>
                        <ButtonConfirm  id="confirm_delete"
                                        labelButton={t('common.button.delete_img')}
                                        confirmationText={t('common.confirm_delete_img')}
                                        labelConfirmButton={t('common.button.confirm')}
                                        handleClickConfirm={this.deleteAvatar}
                                        disabled={!image}/>
                    </div>
                </div>
                <div className="medium-6 cell">
                    <div className="grid-x">
                        <InputValidation    id="nom"
                                            type="text"  
                                            className={"medium-12 cell"}
                                            labelText={t('common.label.name')}
                                            value={nom} 
                                            onChange={handleChange}
                                            validationRule={this.validationRules.nom}
                                            placeholder={t('admin.collectivite.placeholder_type_name')}/>

                        <InputValidation    id="delete_classeur_after"
                                            type="number"
                                            className={"medium-12 cell"}
                                            labelText={t('admin.collectivite.classeur_delay')}
                                            value={delete_classeur_after} 
                                            onChange={handleChange}
                                            placeholder={t('admin.collectivite.placeholder_delay')}
                                            customErrorMessages={this.customErrorMessages.delete_classeur_after}
                                            validationRule={this.validationRules.delete_classeur_after}
                                            helpText={t('admin.collectivite.classeur_delay_help_text')}/>

                        <Switch id="active"
                                className="cell medium-12"
                                labelText={t('common.label.enabled')}
                                checked={active}
                                onChange={handleChange}
                                activeText={t('common.label.yes')}
                                inactiveText={t('common.label.no')}/>
                    </div>
                </div>
                {(id) && 
                <Button id="submit-infos"
                        className="cell medium-12"
                        classNameButton="float-right"
                        onClick={this.saveCollectivite}
                        disabled={!editState}
                        labelText={t('common.button.edit_save')}/>}
            </AccordionItem>
        )
    }
}

export default translate(['sesile'])(CollectiviteInfos)