import React, { Component } from 'react'
import { func } from 'prop-types'
import Validator from 'validatorjs'
import { translate } from 'react-i18next'
import Editor from '../_components/Editor'
import { AccordionItem } from '../_components/AdminUI'
import { Button } from '../_components/Form'

class CollectiviteEmailModels extends Component {
    
    static contextTypes = {
        t: func
    }

    validationRules = {
        message: 'required',
        textmailnew: 'required',
        textmailrefuse: 'required',
        textmailwalid: 'required',
        textcopymailnew: 'required'
    }

    saveCollectivite = () => {
        const { collectivite, putCollectivite } = this.props
        const fields = { 
            message: collectivite.message,
            textmailnew: collectivite.textmailnew,
            textmailrefuse: collectivite.textmailrefuse,
            textmailwalid: collectivite.textmailwalid,
            textcopymailnew: collectivite.textcopymailnew,
            textcopymailwalid: collectivite.textcopymailwalid
        }
        const validation = new Validator(fields, this.validationRules)
        if(validation.passes()) if(collectivite.id) putCollectivite(collectivite.id, fields)
    }

    render() {
        const { t } = this.context
        const { collectivite, handleChange, editState } = this.props
        return (
            <AccordionItem title={t('admin.collectivite.mails_templates')}>
                <Editor id="message" 
                        label={t('admin.collectivite.message')} 
                        className="cell medium-6" 
                        value={collectivite.message} 
                        handleChange={handleChange}/>

                <Editor id="textmailnew"
                        label={t('admin.collectivite.mail.new_classeur')}
                        className="cell medium-6"
                        value={collectivite.textmailnew}
                        handleChange={handleChange}/>

                <Editor id="textmailrefuse"
                        label={t('admin.collectivite.mail.refused_classeur')}
                        className="cell medium-6"
                        value={collectivite.textmailrefuse}
                        handleChange={handleChange}/>

                <Editor id="textmailwalid"
                        label={t('admin.collectivite.mail.valid_classeur')}
                        className="cell medium-6"
                        value={collectivite.textmailwalid}
                        handleChange={handleChange}/>

                <Editor id="textcopymailnew"
                        label={t('admin.collectivite.mail.new_copy_classeur')}
                        className="cell medium-6"
                        value={collectivite.textcopymailnew}
                        handleChange={handleChange}/>

                <Editor id="textcopymailwalid"
                        label={t('admin.collectivite.mail.valid_copy_classeur')}
                        className="cell medium-6"
                        value={collectivite.textcopymailwalid}
                        handleChange={handleChange}/>
                {(collectivite.id) && 
                <Button id="submit-mails"
                        className="cell medium-12"
                        classNameButton="float-right"
                        onClick={this.saveCollectivite}
                        disabled={!editState}
                        labelText={t('common.button.edit_save')}/>}
            </AccordionItem>
        )
    }
}

export default translate(['sesile'])(CollectiviteEmailModels)