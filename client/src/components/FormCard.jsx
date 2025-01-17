import React, {useState} from 'react';
import {Card} from 'primereact/card';
import {Message} from "primereact/message";
import {Button} from "primereact/button";

function FormCard({onSubmit, title = "Register", fields, fieldsState, error, loading, buttonText}) {
    const [incompleteFieldsError, setIncompleteFieldsError] = useState('');

    const handleSubmit = (e) => {
        e.preventDefault();

        const isAnyFieldEmpty = Object.entries(fieldsState)
        .filter(([key, value]) => key !== 'postalCode')  // exclude postal code
        .some(([key, value]) => !value);  // verifica daca exista vreun camp gol
        if (isAnyFieldEmpty) {
            setIncompleteFieldsError('Please fill in all fields!');
            return;
        }

        onSubmit(fieldsState);
        setIncompleteFieldsError('');
    };

    let label = loading ? 'Registering...' : 'Register';
    if (buttonText !== '') {
        label = buttonText;
    }

    return (
        <div className="registerContainer">
            <div className="formContainer">
                <div className="login-container">
                    <Card>

                        <h2 className="formSubtitle">{title}</h2>
                        <form onSubmit={handleSubmit}>

                            {fields}
                            
                            {incompleteFieldsError ? (
                            <Message severity="error" text={incompleteFieldsError} />
                        ) : (
                            error && <Message severity="error" text={error} />
                        )}

                        <Button 
                            type="submit" 
                            disabled={loading}
                            label={label}
                            className="w-full"
                        />


                        </form>

                    </Card>
                </div>
            </div>
        </div>
    );
}

export default FormCard;